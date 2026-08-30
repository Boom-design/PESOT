<?php

namespace App\Console\Commands;

use App\Mail\EmployerInactivityWarning;
use App\Models\Announcement;
use App\Models\EmployerNsrpRegistration;
use App\Models\Staff;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmployerInactivityWarnings extends Command
{
    protected $signature = 'employers:send-inactivity-warnings'
        . ' {--employer= : Ask only this employer, by employer_nsrp_registrations_id}'
        . ' {--as-of= : Judge the month rules as if today were this date (Y-m-d)}';
    protected $description = 'Ask employers who have stopped posting vacancies what their status is — once at one month, again at two';

    // ── PESO, 2026-08-24: "kalas nas oras" — ang kompanya nga nagsara dili
    // ── mag-login para lang magsulti nga nagsara na sila. Mao nga ang opisina
    // ── ang mangutana.
    // ──
    // ── PESO, 2026-08-30: kaduha mangutana ang opisina, dili kausa. Ang unang
    // ── sulat, usa ka bulan, pangutana ra — normal ra ang hilom nga bulan. Ang
    // ── ikaduha, human sa laing bulan, mao ang naay deadline, ug didto pa
    // ── mahibaw-an sa desk nga naay problema. Kining sunod-sunod mao gyud ang
    // ── ginabuhat sa opisina sa kamot; kini nagsulat lang niini.
    // ──
    // ── Usa ka timestamp kada lakang: ang inactivity_notified_at ug ang
    // ── inactivity_second_notified_at. Kung wala kini, ang parehas nga sulat
    // ── moabot kada buntag hangtod motubag sila.
    // ──
    // ── Ang pag-timbre gi-stamp KUNG NAKALARGA ang email. Kung nadaot ang mail
    // ── server ug gi-stamp gihapon, ang orasan modagan alang sa usa ka tawo nga
    // ── walay nadawat nga bisan unsa. ──
    public function handle()
    {
        $first  = (int) config('peso.employer.inactive_months', 1);
        $second = (int) config('peso.employer.inactive_second_months', 2);
        $grace  = (int) config('peso.employer.inactivity_grace_days', 7);

        // ── Ang --as-of dili bypass: samang lagda ra gihapon, gitiman-an lang
        // ── gikan sa laing adlaw. Gikinahanglan kini sa demo, kay ang paghulat
        // ── ug duha ka bulan dili mahimo atubangan sa panel — ug mas maayo kini
        // ── kay sa pag-usab sa petsa sa mga posting sa database.
        $asOf = $this->option('as-of') ? \Carbon\Carbon::parse($this->option('as-of')) : now();
        $only = $this->option('employer');

        // Ang restricted, dormant ug deactivated naa nay mas dako nga problema
        // ug ilang kaugalingon nga mensahe — dili sila samokon niini.
        $employers = EmployerNsrpRegistration::with('employer')
            ->whereNull('dormant_at')
            ->whereNull('inactivity_responded_at')
            ->whereNull('inactivity_second_notified_at')
            ->whereHas('employer', fn($q) => $q->where('status', 'approved'))
            ->when($only, fn($q) => $q->where('employer_nsrp_registrations_id', $only))
            ->get();

        $sentFirst = $sentSecond = $failed = 0;

        foreach ($employers as $employer) {
            $lastPosted = $employer->jobs()->max('created_at');

            // Ang wala pa gyud nakapost, ang petsa sa pagparehistro ang basihan:
            // ang bag-ong employer naay usa ka bulan sa dili pa siya pangutan-on.
            $lastActivity = $lastPosted
                ? \Carbon\Carbon::parse($lastPosted)
                : $employer->created_at;

            if (!$lastActivity) {
                continue;
            }

            $monthsQuiet = (int) $lastActivity->diffInMonths($asOf);

            // Kinsa nga lakang ang angay karon. Ang ikaduha mag-agad sa una:
            // walay employer nga makadawat sa sulat nga naay deadline nga wala
            // pa nakadawat sa pangutana.
            $isFinal = false;

            if ($employer->inactivity_notified_at) {
                // Nadawat na niya ang una. Ang ikaduha mogawas kung nakaabot na
                // ang ikaduhang utlanan.
                if ($monthsQuiet < $second) {
                    continue;
                }
                $isFinal = true;
            } else {
                if ($monthsQuiet < $first) {
                    continue;
                }
            }

            $user = $employer->employer;
            if (!$user || !$user->email) {
                continue;
            }

            $mail = new EmployerInactivityWarning(
                companyName:  $employer->company_name ?? $user->name,
                contactName:  $employer->contact_person ?? $user->name,
                lastPostedOn: $lastPosted ? \Carbon\Carbon::parse($lastPosted)->format('M d, Y') : null,
                disableOn:    $asOf->copy()->addDays($grace)->format('M d, Y'),
                graceDays:    $grace,
                isFinal:      $isFinal,
                monthsQuiet:  $monthsQuiet,
            );

            try {
                Mail::to($user->email)->send($mail);
            } catch (\Throwable $e) {
                Log::error('Employer inactivity warning failed to send', [
                    'employer_id' => $employer->employer_nsrp_registrations_id,
                    'stage'       => $isFinal ? 'second' : 'first',
                    'error'       => $e->getMessage(),
                ]);
                $failed++;
                continue;
            }

            $isFinal
                ? $this->sendSecondNotice($employer, $monthsQuiet, $grace)
                : $this->sendFirstNotice($employer, $monthsQuiet, $second);

            $this->info($employer->company_name . ' — last vacancy '
                . ($lastPosted ? \Carbon\Carbon::parse($lastPosted)->format('Y-m-d') : 'never')
                . ', ' . ($isFinal ? 'second notice sent, desk told.' : 'first notice sent.'));

            $isFinal ? $sentSecond++ : $sentFirst++;
        }

        $this->info("Sent {$sentFirst} first notice(s) and {$sentSecond} second notice(s); {$failed} could not be emailed.");
        return 0;
    }

    /** Month one: a question, and nobody but the employer needs to know. */
    private function sendFirstNotice(EmployerNsrpRegistration $employer, int $months, int $second): void
    {
        $employer->update(['inactivity_notified_at' => now()]);

        Announcement::sendToEmployers([
            'type'           => 'employer_inactivity_warning',
            'title'          => 'Are you still hiring? 📮',
            'message'        => 'PESO has not received a new job vacancy from you for '
                                . $months . ' month' . ($months === 1 ? '' : 's')
                                . '. Please sign in and tell us your status. Posting a new vacancy'
                                . ' also counts as an answer. If we hear nothing, we will write again'
                                . ' at ' . $second . ' months.',
            'reference_type' => 'employer_inactivity',
            'reference_id'   => $employer->employer_nsrp_registrations_id,
        ], $employer->employer_nsrp_registrations_id);
    }

    /**
     * Month two: the same question with a deadline, and the desk is brought in.
     *
     * The desk that owns the account — Job Vacancy for local employers, SRA for
     * overseas — is the one that will decide, so it is told now rather than
     * after the fact. Nothing is disabled here; the week has not run out yet.
     */
    private function sendSecondNotice(EmployerNsrpRegistration $employer, int $months, int $grace): void
    {
        $employer->update(['inactivity_second_notified_at' => now()]);

        Announcement::sendToEmployers([
            'type'           => 'employer_inactivity_warning',
            'title'          => 'Second notice — are you still hiring? 📮',
            'message'        => 'This is our second letter. PESO has not received a new job vacancy'
                                . ' from you for ' . $months . ' months and has had no answer to the'
                                . ' first. Please sign in and tell us your status within ' . $grace
                                . ' days. After that, PESO staff will review your account and may set'
                                . ' it to inactive, which hides your postings. Nothing is deleted.',
            'reference_type' => 'employer_inactivity',
            'reference_id'   => $employer->employer_nsrp_registrations_id,
        ], $employer->employer_nsrp_registrations_id);

        $ownerRole = $employer->is_overseas ? 'sra' : 'job_vacancy';
        $ownerIds  = Staff::where('staff_role', $ownerRole)->pluck('staff_id');

        if ($ownerIds->isEmpty()) {
            return;
        }

        Announcement::sendToStaff([
            'type'           => 'employer_inactivity_second_notice',
            'title'          => 'Employer quiet for ' . $months . ' months ⏳',
            'message'        => ($employer->company_name ?? 'An employer')
                                . ' has not posted a vacancy for ' . $months . ' months and did not'
                                . ' answer the first letter. A second letter has been sent, giving'
                                . ' them ' . $grace . ' days. If they still do not answer, this'
                                . ' account will be yours to switch off.',
            'reference_type' => 'employer_inactivity',
            'reference_id'   => $employer->employer_nsrp_registrations_id,
        ], $ownerIds);
    }
}
