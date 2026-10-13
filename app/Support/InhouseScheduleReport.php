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
            // ── Ang nadesisyunan ra, ug ang nahitabo na ra.
            // ──
            // ── PESO, 2026-10-14: ang listahan naglakip sa naghulat pa, nga
            // ── nagbasa ug "Waiting for a date" sa panid nga report. Ang report
            // ── kay rekord sa nahitabo na; ang request nga wala pa natubag kay
            // ── trabaho, ug ang iyang lugar mao ang Pending In-house Schedule
            // ── nga tab diin naa ang buton. Ang petsa sa umaabot wala pud:
            // ── wala pa man siya nahitabo aron ireport.
            ->whereIn('status', ['accepted', 'rejected'])
            ->whereRaw('COALESCE(confirmed_date, preferred_date) < CURDATE()')
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
