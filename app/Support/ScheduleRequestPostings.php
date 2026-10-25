<?php

namespace App\Support;

use App\Models\InhouseSchedule;
use App\Models\Job;
use Illuminate\Support\Collection;

/**
 * Turns an accepted schedule request into job postings.
 *
 * A schedule request is an employer asking for a day at the PESO Office
 * before it has posted anything — it names the positions and how many
 * applicants it wants to see, and nothing else. Until the desk accepts it,
 * that is all it is.
 *
 * On acceptance the office has committed the day, so each position becomes an
 * in-house posting: the jobseekers can find it, apply to it and be matched
 * against it, and the interview day shows the applicants the employer asked
 * for. Without this the acceptance reached nobody — the employer held a day
 * and the office had to ring people by hand.
 *
 * What the employer never said is not invented here. There are no
 * qualifications on these postings, so every applicant is scored on the
 * office's standard points (Job::MATCH_POINTS), and the employer can open the
 * posting afterwards and add what it wants.
 */
class ScheduleRequestPostings
{
    /**
     * Written into the posting's remarks so these can be found again.
     *
     * A posting the employer wrote itself and one opened from a schedule can
     * carry the same title on the same day, and only the second may be closed
     * again if the desk changes its mind. The tag is what tells them apart.
     */
    private const TAG = 'Opened from in-house schedule request #';

    /** The remark a posting from this schedule carries. */
    public static function tagFor(InhouseSchedule $schedule): string
    {
        return self::TAG . $schedule->inhouse_schedules_id;
    }

    /** The postings this schedule opened, if any. */
    public static function postingsOf(InhouseSchedule $schedule): Collection
    {
        return Job::where('company_id', $schedule->employer_id)
            ->where('remarks', self::tagFor($schedule))
            ->get();
    }

    /**
     * The postings for a schedule, created once.
     *
     * Running this twice does nothing the second time: the postings are found
     * by the schedule's own tag and returned rather than made again. A desk
     * that accepts, rejects and accepts again does not leave the employer
     * with two of everything.
     */
    public static function open(InhouseSchedule $schedule, ?int $staffId = null): Collection
    {
        $positions = collect($schedule->job_positions ?? [])
            ->map(fn($p) => trim((string) $p))
            ->filter()
            ->unique()
            ->values();

        if ($positions->isEmpty()) {
            return collect();
        }

        $employer = $schedule->employer;
        $start    = $schedule->confirmed_date ?: $schedule->preferred_date;

        if (!$start) {
            return collect();
        }

        $start = \Carbon\Carbon::parse($start);
        $end   = $schedule->preferred_date_last ?: $start;

        // The only number the employer gave is how many applicants it wants to
        // see. Split across the positions, it is the nearest honest answer to
        // "how many openings"; the employer can correct it on the posting.
        $slots = max(1, (int) ceil(((int) $schedule->num_applicants ?: 1) / $positions->count()));

        $already = self::postingsOf($schedule)->keyBy('title');
        $made    = collect();

        foreach ($positions as $title) {
            if ($already->has($title)) {
                // A desk that accepts again reopens what it closed, rather
                // than adding a second copy of the same vacancy.
                $posting = $already->get($title);

                if ($posting->status === 'closed' && !$start->isPast()) {
                    $posting->update(['status' => 'open']);
                }

                $made->push($posting);
                continue;
            }

            $made->push(Job::create([
                'company_id'         => $schedule->employer_id,
                'title'              => $title,
                'description'        => 'Posted from the in-house interview scheduled for '
                                        . $schedule->schedule_window_label . '. Contact PESO Cagayan de Oro '
                                        . 'or the employer for the full details of the position.',
                'location'           => $employer->est_city_municipality ?: 'Cagayan de Oro City',
                'type'               => 'permanent',
                'industry_group'     => $employer->industry_group,
                'slots'              => $slots,
                'salary'             => null,
                'deadline'           => $start->toDateString(),
                'status'             => $start->isPast() ? 'closed' : 'open',
                'schedule_type'      => 'inhouse',
                'posting_status'     => 'approved',
                'reviewed_by'        => $staffId,
                'reviewed_at'        => now(),
                'posting_type'       => 'direct',
                'preferred_date'     => $start->toDateString(),
                'preferred_date_end' => $end->toDateString(),
                'confirmed_date'     => $schedule->confirmed_date,
                'preferred_time'     => $schedule->confirmed_time ?: $schedule->preferred_time,
                'venue_type'         => $schedule->venue_type === 'peso_office' ? 'peso_office' : 'other',
                'venue_address'      => $schedule->venue_type === 'peso_office' ? null : $schedule->venue_address,
                'remarks'            => self::tagFor($schedule),
            ]));
        }

        return $made;
    }

    /**
     * Close what the schedule opened.
     *
     * A desk may accept a request and then reject it — the employer's papers
     * lapse, the day is needed for something else. The interview is off, so
     * the vacancies that only existed for that interview come down. They are
     * closed rather than deleted: jobseekers applied to them, and those
     * applications are the office's record.
     */
    public static function close(InhouseSchedule $schedule): int
    {
        $closed = 0;

        foreach (self::postingsOf($schedule) as $posting) {
            if ($posting->status === 'closed') {
                continue;
            }

            $posting->update(['status' => 'closed']);
            $closed++;
        }

        return $closed;
    }
}
