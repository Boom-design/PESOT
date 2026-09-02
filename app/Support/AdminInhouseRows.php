<?php

namespace App\Support;

use App\Models\Application;
use App\Models\InhouseSchedule;
use App\Models\Job;
use Illuminate\Support\Collection;

/**
 * Every in-house interview the office knows about, from both doors.
 *
 * An employer can ask for the PESO Office two ways, and the system keeps them
 * in two tables:
 *
 *   1. A schedule-only request — `inhouse_schedules`. The employer asks for
 *      days without attaching a vacancy to them.
 *   2. A job posting whose `schedule_type` is `inhouse` — `job_qualifications`.
 *      The vacancy and the days it needs are asked for together.
 *
 * PESO admin, 2026-09-01: the Job Activities page read only the first table, so
 * an in-house interview that LRA had already approved showed up nowhere — the
 * tab was empty while the schedule sat confirmed on the LRA page, which reads
 * both. An admin looking for the office's own bookings was reading half a book.
 *
 * The two tables answer the same questions with different column names, so
 * each row is flattened here into one shape and the page renders that. Doing
 * it in the view instead would mean an `if` on every cell.
 */
class AdminInhouseRows
{
    /** Newest first — the admin is reading what is happening, not a ledger. */
    public static function all(): Collection
    {
        return self::fromSchedules()
            ->concat(self::fromPostings())
            ->sortByDesc('sort_date')
            ->values();
    }

    /** Door 1 — the employer asked for days and named the positions in text. */
    private static function fromSchedules(): Collection
    {
        return InhouseSchedule::with('employer.employer')
            ->get()
            ->map(function (InhouseSchedule $s) {
                $employer = $s->employer;

                // The positions on a schedule request are names the employer
                // typed. Where a posting of that name exists we show its slots
                // and pay beside it; where it does not, the name still gets a
                // row — a position with no posting is what this page is for.
                $postings = $employer
                    ? Job::where('company_id', $s->employer_id)->get()
                    : collect();

                return (object) [
                    'row_key'         => 'sched-' . $s->inhouse_schedules_id,
                    'source_label'    => 'Schedule request',
                    'company'         => $employer,
                    'window_label'    => $s->confirmed_date
                        ? $s->confirmed_date->format('M d, Y')
                        : $s->schedule_window_label,
                    'requested_label' => $s->schedule_window_label,
                    'time_label'      => self::time($s->preferred_time),
                    'confirmed_date'  => optional($s->confirmed_date)->format('M d, Y'),
                    'confirmed_time'  => self::time($s->confirmed_time),
                    'applicant_count' => $s->num_applicants,
                    'offer_count'     => self::offersForEmployer($s->employer_id, $s->inhouse_schedules_id),
                    'state'           => $s->status,
                    'decline_reason'  => $s->rejection_reason,
                    'venue_label'     => self::venue($s->venue_type, $s->venue_address),
                    'notes'           => $s->notes,
                    'positions'       => collect($s->job_positions ?? [])->filter()->values(),
                    'postings'        => $postings,
                    'sort_date'       => $s->created_at,
                ];
            });
    }

    /**
     * Door 2 — the vacancy and its days came in together.
     *
     * `posting_status` is the office's answer here, the same way `status` is on
     * a schedule request: approved means the days are held, rejected means they
     * were refused, pending means nobody has answered yet.
     */
    private static function fromPostings(): Collection
    {
        return Job::with('company.employer')
            ->where('schedule_type', 'inhouse')
            ->get()
            ->map(function (Job $job) {
                $hired = Application::where('job_id', $job->job_qualifications_id)
                    ->where('status', 'hired')
                    ->count();

                return (object) [
                    'row_key'         => 'job-' . $job->job_qualifications_id,
                    'source_label'    => 'Job posting',
                    'company'         => $job->company,
                    'window_label'    => $job->confirmed_date
                        ? $job->confirmed_date->format('M d, Y')
                        : $job->schedule_window_label,
                    'requested_label' => $job->schedule_window_label,
                    'time_label'      => self::time($job->preferred_time),
                    'confirmed_date'  => optional($job->confirmed_date)->format('M d, Y'),
                    // A posting carries one time, and approving it does not
                    // change the hour — the confirmed time is the asked-for one.
                    'confirmed_time'  => $job->posting_status === 'approved'
                        ? self::time($job->preferred_time)
                        : null,
                    'applicant_count' => $job->applications()->count(),
                    'offer_count'     => $hired,
                    'state'           => match ($job->posting_status) {
                        'approved' => 'accepted',
                        'rejected' => 'rejected',
                        default    => 'pending',
                    },
                    'decline_reason'  => $job->remarks,
                    'venue_label'     => self::venue($job->venue_type, $job->venue_address),
                    'notes'           => null,
                    'positions'       => collect([$job->title]),
                    'postings'        => collect([$job]),
                    'sort_date'       => $job->created_at,
                ];
            });
    }

    /**
     * Job offers made off the back of a schedule request.
     *
     * The employer's hires that were recorded against this interview: someone
     * who was on the day's list and ended up hired for one of that company's
     * vacancies.
     */
    private static function offersForEmployer($employerId, $scheduleId): int
    {
        return \Illuminate\Support\Facades\DB::table('inhouse_participants')
            ->where('inhouse_schedule_id', $scheduleId)
            ->whereIn('jobseeker_id', function ($q) use ($employerId) {
                $q->select('jobseeker_id')->from('job_matching')
                  ->where('status', 'hired')
                  ->whereIn('job_id', function ($jq) use ($employerId) {
                      $jq->select('job_qualifications_id')->from('job_qualifications')
                         ->where('company_id', $employerId);
                  });
            })
            ->count();
    }

    /** The two tables spell the same venue differently — 'other' and 'custom'. */
    private static function venue(?string $type, ?string $address): string
    {
        return in_array($type, ['other', 'custom'], true)
            ? ($address ?: 'Other venue')
            : 'PESO Office';
    }

    private static function time($value): ?string
    {
        return $value ? \Carbon\Carbon::parse($value)->format('h:i A') : null;
    }
}
