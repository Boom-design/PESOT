<?php

namespace App\Support;

use App\Models\InhouseSchedule;

/**
 * Which employers got a room, and which were turned down.
 *
 * The LRA and SRA reports counted in-house interviews — how many applicants
 * registered, how many were placed — but never listed the requests themselves.
 * An employer who asked for a date and was declined left no trace on any
 * report, so "who did we say no to last month, and why" could not be answered
 * from the page at all.
 *
 * `$overseas` picks the desk: false is the LRA's calendar, true is the SRA's.
 */
class InhouseScheduleReport
{
    public static function query(bool $overseas, ?string $search = null)
    {
        return InhouseSchedule::with('employer')
            ->whereHas('employer', fn($q) => $q->where('is_overseas', $overseas))
            ->when($search, fn($q) => $q->whereHas('employer',
                fn($e) => $e->where('company_name', 'like', "%{$search}%")))
            // Newest interview first. A request the office has not answered yet
            // has no confirmed date, so it is ordered by the day the employer
            // asked for — which is the day that is about to arrive.
            ->orderByRaw('COALESCE(confirmed_date, preferred_date) DESC');
    }

    /** Five rows a page, the same as the Company Interview report. */
    public static function paginate(bool $overseas, ?string $search = null)
    {
        return self::query($overseas, $search)->paginate(5, ['*'], 'page')->withQueryString();
    }
}
