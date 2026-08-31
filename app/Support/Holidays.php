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
