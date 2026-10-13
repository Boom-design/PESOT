<?php

namespace App\Support;

/**
 * Philippine holidays, computed locally.
 *
 * No API and no paid feed: the system has to keep working after deployment
 * with nothing bought, so the dates that follow a rule are calculated here and
 * the ones that are proclaimed each year are typed into config/peso.php.
 *
 * The office is not blocked on any of this. A holiday is drawn red on the
 * activity calendars as a warning, never as a lock — employers do ask for
 * in-house interviews and job fairs on rest days, and staff may book them.
 */
class Holidays
{
    /** Fixed-date holidays: [month, day, name]. */
    private const FIXED = [
        [1,  1,  "New Year's Day"],
        [4,  9,  'Araw ng Kagitingan'],
        [5,  1,  'Labor Day'],
        [6,  12, 'Independence Day'],
        [8,  21, 'Ninoy Aquino Day'],
        [11, 1,  "All Saints' Day"],
        [11, 2,  "All Souls' Day"],
        [11, 30, 'Bonifacio Day'],
        [12, 8,  'Immaculate Conception'],
        [12, 24, 'Christmas Eve'],
        [12, 25, 'Christmas Day'],
        [12, 30, 'Rizal Day'],
        [12, 31, "New Year's Eve"],
    ];

    /**
     * ['Y-m-d' => 'Name'] for the given years, sorted by date.
     */
    public static function forYears(array $years): array
    {
        $out = [];

        foreach ($years as $year) {
            $year = (int) $year;

            foreach (self::FIXED as [$month, $day, $name]) {
                $out[sprintf('%04d-%02d-%02d', $year, $month, $day)] = $name;
            }

            // Holy Week moves with Easter.
            $easter = self::easter($year);
            $out[self::shift($easter, -3)] = 'Maundy Thursday';
            $out[self::shift($easter, -2)] = 'Good Friday';
            $out[self::shift($easter, -1)] = 'Black Saturday';

            // National Heroes Day — the last Monday of August.
            $out[date('Y-m-d', strtotime("last monday of august $year"))] = 'National Heroes Day';
        }

        // Proclaimed dates (Eid'l Fitr, Eid'l Adha, local charter days, any
        // one-off proclamation). Typed by the office; they win over the
        // computed list so a correction is always possible.
        foreach ((array) config('peso.holidays.extra', []) as $date => $name) {
            $out[$date] = $name;
        }

        ksort($out);

        return $out;
    }

    /** Current year plus the neighbours, so paging the calendar stays covered. */
    public static function aroundNow(): array
    {
        $year = (int) now()->year;

        return self::forYears([$year - 1, $year, $year + 1, $year + 2]);
    }

    /**
     * Why the office is shut on this day, or null when it is open.
     *
     * PESO, 2026-10-13: Sunday is closed and Saturday is not. The holidays were
     * already refused, by the pickers and again on save; Sunday was not refused
     * anywhere, so an employer could ask for one and a desk could confirm it.
     *
     * One method, because the two rules were in different places: the holiday
     * list was read straight out of aroundNow() by four screens and two
     * controllers, and nothing anywhere knew about Sunday. A seventh caller
     * that remembers one rule and forgets the other is how they drift.
     */
    public static function closedReason(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            $day = \Carbon\Carbon::parse($date);
        } catch (\Throwable $e) {
            return null; // Not a date. The date rule will refuse it.
        }

        $holidays = self::aroundNow();
        $key      = $day->toDateString();

        if (isset($holidays[$key])) {
            return $holidays[$key];
        }

        return $day->isSunday() ? 'Sunday' : null;
    }

    /**
     * The closed days inside the window the calendars draw, as
     * ['Y-m-d' => 'reason'] — the holidays plus every Sunday.
     *
     * The pickers disable whatever is in here, so they do not each have to work
     * out the week for themselves.
     */
    public static function closedDays(): array
    {
        $closed = self::aroundNow();

        $cursor = \Carbon\Carbon::create((int) now()->year - 1, 1, 1)->startOfWeek(\Carbon\CarbonInterface::SUNDAY);
        $end    = \Carbon\Carbon::create((int) now()->year + 2, 12, 31);

        for (; $cursor->lte($end); $cursor->addWeek()) {
            $key = $cursor->toDateString();
            if (!isset($closed[$key])) {
                $closed[$key] = 'Sunday';
            }
        }

        ksort($closed);

        return $closed;
    }

    private static function shift(string $date, int $days): string
    {
        return date('Y-m-d', strtotime($date . ' ' . $days . ' days'));
    }

    /**
     * Easter Sunday, anonymous Gregorian algorithm.
     *
     * Written out rather than calling easter_date(): that needs ext/calendar,
     * which is not guaranteed on the machine this is deployed to.
     */
    private static function easter(int $year): string
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);

        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day   = (($h + $l - 7 * $m + 114) % 31) + 1;

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
}
