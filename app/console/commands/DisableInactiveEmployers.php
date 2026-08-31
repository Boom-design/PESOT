<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\EmployerNsrpRegistration;
use App\Models\Staff;
use Illuminate\Console\Command;

class DisableInactiveEmployers extends Command
{
    protected $signature = 'employers:disable-inactive'
        . ' {--employer= : Sweep only this employer, by employer_nsrp_registrations_id}'
        . ' {--as-of= : Judge the one-week grace as if today were this date (Y-m-d)}';
    protected $description = 'Hand employers who never answered the second notice to the desk that owns them';

    // ── Ang ikatulong lakang. Ang una ug ikaduha nangutana; kini nag-abot sa
    // ── desisyon ngadto sa tawo.
    // ──
    // ── PESO, 2026-08-30: WALA NAY AWTOMATIKO NGA PAG-DISABLE. Kaniadto kining
    // ── sugo mismo ang nagpatay sa account paglabay sa usa ka semana. Dili kana
    // ── ang buhat sa opisina: ang tawo sa Job Vacancy (local) o SRA (overseas)
    // ── ang mohukom, kay siya ra ang nakahibalo kung naay gitawag, gibisita, o
    // ── nakigsulti nga wala nahisulat sa sistema. Kining sugo mopahibalo na
    // ── lang nga human na ang palugit ug iya na ang account.
    // ──
    // ── Ang inactivity_disable_prompted_at mao ang marka nga napahibalo na sila.
    // ── Kung wala kini, ang parehas nga pahibalo moabot kada buntag hangtod may
    // ── molihok — ug ang bell sa staff mapuno sa usa ra ka employer. ──
    public function handle()
    {
        $grace = (int) config('peso.employer.inactivity_grace_days', 7);

        // ── Samang tumong sa --as-of sa pahimangno: ang usa ka semana nga
        // ── palugit gitiman-an gikan sa gihatag nga adlaw, apan ang tanang
        // ── gisulat sa database tinuod nga karon. ──
        $asOf   = $this->option('as-of') ? \Carbon\Carbon::parse($this->option('as-of')) : now();
        $cutoff = $asOf->copy()->subDays($grace);
        $only   = $this->option('employer');

        $employers = EmployerNsrpRegistration::with('employer')
            ->whereNotNull('inactivity_second_notified_at')
            ->whereNull('dormant_at')
            ->when($only, fn($q) => $q->where('employer_nsrp_registrations_id', $only))
            ->get();

        $handed = $reset = $waiting = 0;

        foreach ($employers as $employer) {
            $notifiedAt = $employer->inactivity_second_notified_at;

            // 1. Nakapost siya human sa pangutana. Kana ang tubag, ug mas klaro
            //    pa kay sa bisan unsang sulat.
            $postedSince = $employer->jobs()
                ->where('created_at', '>', $employer->inactivity_notified_at ?? $notifiedAt)
                ->exists();

            if ($postedSince) {
                $employer->update([
                    'inactivity_notified_at'         => null,
                    'inactivity_second_notified_at'  => null,
                    'inactivity_disable_prompted_at' => null,
                    'inactivity_responded_at'        => null,
                    'inactivity_status'              => null,
                    'inactivity_response'            => null,
                ]);
                $this->info($employer->company_name . ' — posted again, clock cleared.');
                $reset++;
                continue;
            }

            // 2. Nisulat siya sa sistema. Ang staff na ang mo-desisyon, ug naa
            //    na sa Approved tab ang iyang tubag.
            if ($employer->inactivity_responded_at) {
                $waiting++;
                continue;
            }

            // 3. Napahibalo na ang desk mahitungod niini. Ayaw na balik-balika.
            if ($employer->inactivity_disable_prompted_at) {
                $waiting++;
                continue;
            }

            // 4. Wala gyud, ug human na ang palugit.
            if ($notifiedAt->gt($cutoff)) {
                continue;
            }

            $user = $employer->employer;
            if (!$user) {
                continue;
            }

            $employer->update(['inactivity_disable_prompted_at' => now()]);

            // ── Ang employer masayod nga nahuman na ang iyang semana. ──
            //
            // Ang ikaduhang sulat naghatag kaniya ug pito ka adlaw. Kung
            // molabay kadto nga walay bisan unsa nga moabot, ang hilom mabasa
            // isip pag-uyon — ug ang sunod niyang mabati mao na ang na-tago
            // nga mga bakante. Kini ang katapusang pag-tuktok sa dili pa ang
            // desk mohukom, ug nagsulti gihapon kung unsa ang makapahunong
            // niini: mo-sign in ug motubag, o mag-post ug bakante.
            Announcement::sendToEmployers([
                'type'           => 'employer_inactivity_final_notice',
                'title'          => 'Your ' . $grace . '-day grace has ended ⏰',
                'message'        => 'PESO gave you ' . $grace . ' days to tell us your status and has'
                                    . ' had no answer. Your account is now with PESO staff, who may'
                                    . ' set it to inactive — that hides your vacancies from jobseekers.'
                                    . ' Nothing is deleted, and you can still stop this: sign in and'
                                    . ' tell us your status, or post a new vacancy.',
                'reference_type' => 'employer_inactivity',
                'reference_id'   => $employer->employer_nsrp_registrations_id,
            ], $employer->employer_nsrp_registrations_id);

            // ── Ang tibuok opisina masayod, apan usa ra ang makahimo. ──
            //
            // Ang lokal kay sa Job Vacancy, ang overseas kay sa SRA — samang
            // pagpili nga gigamit sa tibuok sistema. Sila ang makadawat sa
            // notice nga naay link ngadto sa employer.
            //
            // Ang duha ka lain nga desk makadawat sa parehas nga balita, apan
            // ang reference_type null: ang bell mo-fall through ngadto sa
            // notifications page, kay dili man nila mabuhat ang aksyon.
            $ownerRole = $employer->is_overseas ? 'sra' : 'job_vacancy';
            $ownerName = $employer->is_overseas ? 'SRA' : 'Job Vacancy';

            $summary = ($employer->company_name ?? 'An employer')
                     . ' was written to twice and has not answered. The ' . $grace
                     . '-day grace ended on ' . $notifiedAt->copy()->addDays($grace)->format('M d, Y') . '.';

            $ownerIds = Staff::where('staff_role', $ownerRole)->pluck('staff_id');
            if ($ownerIds->isNotEmpty()) {
                Announcement::sendToStaff([
                    'type'           => 'employer_inactivity_for_disabling',
                    'title'          => 'Employer ready to be switched off 🔒',
                    'message'        => $summary . ' Open Employers → Approved Employers, use Update on'
                                        . ' this company and set the account to inactive — or give them'
                                        . ' more time if you know something the system does not.',
                    'reference_type' => 'employer_inactivity',
                    'reference_id'   => $employer->employer_nsrp_registrations_id,
                ], $ownerIds);
            }

            $otherIds = Staff::whereIn('staff_role', ['job_vacancy', 'sra', 'lra'])
                ->where('staff_role', '!=', $ownerRole)
                ->pluck('staff_id');
            if ($otherIds->isNotEmpty()) {
                Announcement::sendToStaff([
                    'type'           => 'employer_inactivity_for_disabling',
                    'title'          => 'Employer ready to be switched off 🔒',
                    'message'        => $summary . ' ' . $ownerName . ' staff handle this account.',
                    'reference_type' => null,
                    'reference_id'   => null,
                ], $otherIds);
            }

            $this->info($employer->company_name . ' — grace over, handed to ' . $ownerName . '.');
            $handed++;
        }

        $this->info("Handed {$handed} employer(s) to staff; {$reset} posted again; {$waiting} already with staff.");
        return 0;
    }
}
