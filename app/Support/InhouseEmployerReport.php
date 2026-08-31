<?php

namespace App\Support;

use App\Models\Application;
use App\Models\InhouseSchedule;
use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Support\Collection;

// ── Ang report sa usa ka employer human sa iyang in-house interview.
// ──
// ── LRA staff, 2026-08-23: "haya ra makita ni LRA staff ang report sa specific
// ── na employer 1 week after sa iyang in-house interview... ang report sa
// ── specific na employer / status sa jobseeker."
// ──
// ── Ang usa ka semana dili paglangan — panahon kana sa employer. Human sa
// ── interview, ang employer pa ang mo-desisyon kinsa ang iyang dawaton, ug ang
// ── report nga basahon sa ikaduhang adlaw usa ka listahan sa blangko. Sa
// ── ikapito, may sulod na siya.
// ──
// ── Walay bag-ong porma nga pun-on sa employer. Gi-update na nila ang status sa
// ── matag aplikante sa ilang kaugalingon nga screen; kining klase mobasa ra
// ── niini. Ang porma nga mangayo pag-usab sa parehas nga tubag mao ang porma
// ── nga dili pun-on, ug ang report mahimong blangko hangtod sa hangtod. ──
class InhouseEmployerReport
{
    /** Ang kolum sa CSV — parehas gyud sa makita sa screen. */
    public const COLUMNS = [
        'Company', 'Employer Type', 'Interview Date', 'Venue', 'Position',
        'Jobseeker', 'Contact', 'Match %', 'Result',
    ];

    /** Ang tubag sa employer, sa pinulongan nga masabtan sa staff. */
    public const RESULT_LABELS = [
        'hired'     => 'Hired',
        'rejected'  => 'Not hired',
        'waiting'   => 'Waiting for the employer',
        'qualified' => 'Waiting for the employer',
        'reviewed'  => 'Waiting for the employer',
        'pending'   => 'No decision recorded',
    ];

    public static function delayDays(): int
    {
        return (int) config('peso.schedule.report_delay_days');
    }

    /**
     * The day this interview's report opens to staff.
     *
     * Counted from the day the interview actually happened — confirmed_date
     * when staff set one, otherwise the first day of the window the employer
     * offered. Job::interview_date already makes that choice.
     */
    public static function visibleFrom(Carbon $interviewDate): Carbon
    {
        return $interviewDate->copy()->addDays(self::delayDays());
    }

    public static function isVisible(?Carbon $interviewDate): bool
    {
        return $interviewDate !== null && self::visibleFrom($interviewDate)->lte(today());
    }

    /**
     * The in-house job postings whose report has opened.
     *
     * Only approved ones: a posting LRA rejected never had an interview, and a
     * pending one has not been agreed to yet.
     */
    public static function completedPostings(bool $overseas, ?string $search = null): Collection
    {
        $cutoff = today()->subDays(self::delayDays());

        return Job::with(['company'])
            ->where('schedule_type', 'inhouse')
            ->where('posting_status', 'approved')
            ->whereHas('company', fn($q) => $q->where('is_overseas', $overseas))
            ->when($search, fn($q) => $q->whereHas('company', fn($c) =>
                $c->where('company_name', 'like', "%{$search}%")))
            ->get()
            // Ang interview_date usa ka accessor (confirmed_date o preferred_date),
            // mao nga dili siya masala sa SQL nga walay pagdoble sa maong pagpili.
            ->filter(fn(Job $job) => $job->interview_date
                && $job->interview_date->lte($cutoff))
            ->sortByDesc(fn(Job $job) => $job->interview_date)
            ->values();
    }

    /**
     * Who actually took part.
     *
     * inhouse_participation = 'accepted' is the same test the employer's own
     * applicant screen and the jobseeker's schedule list already use for
     * "taking part in this in-house interview". An applicant who never answered
     * the prompt, or declined it, did not sit the interview and does not belong
     * in a report about it.
     */
    public static function attendees(Job $job): Collection
    {
        return Application::with('jobseeker')
            ->where('job_id', $job->job_qualifications_id)
            ->where('inhouse_participation', 'accepted')
            ->get()
            ->sortBy(fn($a) => mb_strtolower(trim(
                ($a->jobseeker->surname ?? '') . ' ' . ($a->jobseeker->first_name ?? ''))))
            ->values();
    }

    /** Pila ang niapil, ug pila ang na-hire — ang duha ka numero nga gipangita. */
    public static function totals(Collection $attendees): array
    {
        return [
            'interviewed' => $attendees->count(),
            'hired'       => $attendees->where('status', 'hired')->count(),
            'undecided'   => $attendees->whereIn('status', ['pending', 'reviewed', 'qualified', 'waiting'])->count(),
        ];
    }

    /**
     * Room bookings with no vacancy attached.
     *
     * A schedule-only request reserves the office without posting a job, so
     * there are no applications to report on. They are listed anyway, with the
     * positions the employer named — dropping them silently would make the page
     * disagree with the calendar it is meant to explain.
     */
    public static function completedScheduleOnly(bool $overseas, ?string $search = null): Collection
    {
        $cutoff = today()->subDays(self::delayDays());

        return InhouseSchedule::with('employer')
            ->where('status', 'accepted')
            ->whereHas('employer', fn($q) => $q->where('is_overseas', $overseas))
            ->when($search, fn($q) => $q->whereHas('employer', fn($c) =>
                $c->where('company_name', 'like', "%{$search}%")))
            ->get()
            ->filter(function (InhouseSchedule $s) use ($cutoff) {
                $date = $s->confirmed_date ?: $s->preferred_date;
                return $date && Carbon::parse($date)->lte($cutoff);
            })
            ->sortByDesc(fn(InhouseSchedule $s) => $s->confirmed_date ?: $s->preferred_date)
            ->values();
    }

    /** Usa ka laray kada jobseeker, andam na para sa spreadsheet. */
    public static function rows(bool $overseas, ?string $search = null): Collection
    {
        $rows = collect();

        foreach (self::completedPostings($overseas, $search) as $job) {
            $attendees = self::attendees($job);

            if ($attendees->isEmpty()) {
                $rows->push([
                    $job->company->company_name ?? '',
                    $job->company->employer_type ?? '',
                    $job->interview_date->format('Y-m-d'),
                    $job->venue_type === 'other' ? ($job->venue_address ?? '') : 'PESO Office',
                    $job->title,
                    '', '', '',
                    'No jobseeker took part',
                ]);
                continue;
            }

            foreach ($attendees as $application) {
                $seeker = $application->jobseeker;
                $rows->push([
                    $job->company->company_name ?? '',
                    $job->company->employer_type ?? '',
                    $job->interview_date->format('Y-m-d'),
                    $job->venue_type === 'other' ? ($job->venue_address ?? '') : 'PESO Office',
                    $job->title,
                    trim(($seeker->first_name ?? '') . ' ' . ($seeker->surname ?? '')),
                    $seeker->contact_number ?? '',
                    $application->match_percentage !== null
                        ? number_format((float) $application->match_percentage, 0) . '%'
                        : '',
                    self::RESULT_LABELS[$application->status] ?? ucfirst((string) $application->status),
                ]);
            }
        }

        foreach (self::completedScheduleOnly($overseas, $search) as $schedule) {
            $date = $schedule->confirmed_date ?: $schedule->preferred_date;
            $rows->push([
                $schedule->employer->company_name ?? '',
                $schedule->employer->employer_type ?? '',
                Carbon::parse($date)->format('Y-m-d'),
                $schedule->venue_type === 'custom' ? ($schedule->venue_address ?? '') : 'PESO Office',
                collect((array) $schedule->job_positions)->implode(', ') ?: 'Not stated',
                '', '', '',
                'Room booked only — no vacancy posted through the system',
            ]);
        }

        return $rows;
    }
}
