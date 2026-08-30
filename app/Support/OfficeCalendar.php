<?php

namespace App\Support;

use App\Models\OfficeCalendarEvent;
use Illuminate\Support\Collection;

/**
 * The days the PESO office is occupied, and the one rule that reads them.
 *
 * A holiday is drawn red and blocks nothing — the office may still take a
 * booking on a rest day. An entry here is different: the staff who would run
 * the in-house interview or the job fair are in a meeting, so the day is not
 * available and the booking is refused.
 *
 * Every caller asks this class, so the calendar drawing and the validation
 * cannot disagree about which days are taken.
 */
class OfficeCalendar
{
    /**
     * The earliest date an employer may book.
     *
     * Not today: the office has preparation to do before an employer arrives —
     * the room, the applicants to call, the staff to free — and none of that
     * fits into the same day the request comes in. One place so the validation
     * rule, the date pickers and the error message cannot disagree.
     */
    public static function earliestBookable(): \Carbon\Carbon
    {
        return \Carbon\Carbon::today()->addDays((int) config('peso.schedule.min_lead_days', 1));
    }

    /** The `after_or_equal:` argument for a bookable date. */
    public static function earliestBookableDate(): string
    {
        return self::earliestBookable()->toDateString();
    }

    public static function leadTimeMessage(): string
    {
        return 'Bookings need lead time for PESO to prepare — the earliest date you can pick is '
             . self::earliestBookable()->format('M d, Y') . '.';
    }

    /**
     * ['Y-m-d' => ['title' => …, 'type' => …, 'label' => …]] for every day the
     * office is occupied, multi-day entries expanded into one key per day.
     */
    public static function blockedDates(): array
    {
        $out = [];

        foreach (OfficeCalendarEvent::orderBy('start_date')->get() as $event) {
            foreach (self::daysOf($event) as $iso) {
                $out[$iso] = [
                    'title' => $event->title,
                    'type'  => $event->type,
                    'label' => $event->type_label,
                ];
            }
        }

        ksort($out);

        return $out;
    }

    /** Every day an entry covers, start and end inclusive. */
    public static function daysOf(OfficeCalendarEvent $event): array
    {
        $days   = [];
        $cursor = $event->start_date->copy();
        $last   = $event->last_date;

        // A corrupt row with the end before the start would otherwise loop for
        // ever; treat it as the single day it started on.
        if ($last->lt($cursor)) {
            return [$cursor->toDateString()];
        }

        while ($cursor->lte($last)) {
            $days[] = $cursor->toDateString();
            $cursor->addDay();
        }

        return $days;
    }

    /** The entries covering a given day, if any. */
    public static function eventsOn(string $date): Collection
    {
        return OfficeCalendarEvent::whereDate('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')->whereDate('start_date', '=', $date)
                  ->orWhereDate('end_date', '>=', $date);
            })
            ->orderBy('start_date')
            ->get();
    }

    public static function isBlocked(?string $date): bool
    {
        if (!$date) return false;

        return self::eventsOn($date)->isNotEmpty();
    }

    /**
     * Why a day is unavailable, phrased for whoever is being turned away.
     *
     * The employer gets the plain fact that PESO is unavailable and nothing
     * about what the office is doing that day; staff get the title, because
     * they need to know which meeting it is.
     */
    public static function reason(string $date, bool $forStaff = false): ?string
    {
        $events = self::eventsOn($date);
        if ($events->isEmpty()) return null;

        $when = \Carbon\Carbon::parse($date)->format('M d, Y');

        if (!$forStaff) {
            return 'PESO is not available on ' . $when . '. Please choose another date.';
        }

        $first = $events->first();
        $more  = $events->count() > 1 ? ' (+' . ($events->count() - 1) . ' more)' : '';

        return $first->type_label . ' — ' . $first->title . ' on ' . $when . $more . '.';
    }

    /**
     * The days inside a window that the office cannot take.
     *
     * Used when an employer proposes a range: the whole window is refused only
     * if nothing is left in it, and the message names the days that are gone.
     *
     * @return string[] ISO dates
     */
    public static function blockedWithin(string $start, string $end): array
    {
        $blocked = self::blockedDates();
        $days    = [];
        $cursor  = \Carbon\Carbon::parse($start);
        $last    = \Carbon\Carbon::parse($end);

        while ($cursor->lte($last)) {
            $iso = $cursor->toDateString();
            if (isset($blocked[$iso])) $days[] = $iso;
            $cursor->addDay();
        }

        return $days;
    }
}
