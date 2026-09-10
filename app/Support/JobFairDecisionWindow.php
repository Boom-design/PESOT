<?php

namespace App\Support;

use App\Models\JobFairEvent;
use Carbon\Carbon;

/**
 * How long an employer may keep deciding on the people it met at a job fair.
 *
 * The window has two ends and both matter.
 *
 * It opens on the day of the fair. Nobody is hired, waitlisted or rejected
 * before then, because none of those decisions has been made yet — the
 * employer has not met anybody.
 *
 * It closes a month after. Not every decision is made at the booth: an
 * employer who says "come to our office next week" marks the applicant
 * Waiting, and the hire lands days or weeks later. That later hire is what the
 * Company Placement report counts, so the buttons cannot simply die when the
 * fair ends.
 *
 * PESO Job Fair staff, 2026-09-04: the paper report asks "Status After One (1)
 * Month — Hired / Not Hired", and once the office has phoned around and filed
 * it, the answer is on record. A hire recorded in the fourteenth month reaches
 * no report; it only rewrites history where nobody is reading. So the month
 * the office already works to is the month the buttons stay live.
 */
class JobFairDecisionWindow
{
    /** Days after the fair that the employer may still decide. */
    public static function days(): int
    {
        return (int) config('peso.jobfair.placement_window_days', 30);
    }

    public static function opensOn(JobFairEvent $event): Carbon
    {
        return $event->event_date->copy()->startOfDay();
    }

    public static function closesOn(JobFairEvent $event): Carbon
    {
        return self::opensOn($event)->addDays(self::days())->endOfDay();
    }

    /**
     * 'early' before the fair, 'open' inside the window, 'closed' after it.
     *
     * A posting with no fair behind it returns 'open'. That is not a job fair
     * decision at all, and this class must not lock a screen it has no say
     * over.
     */
    public static function state(?JobFairEvent $event): string
    {
        if (!$event || !$event->event_date) {
            return 'open';
        }

        return match (true) {
            now()->lt(self::opensOn($event))  => 'early',
            now()->gt(self::closesOn($event)) => 'closed',
            default                           => 'open',
        };
    }

    public static function isOpen(?JobFairEvent $event): bool
    {
        return self::state($event) === 'open';
    }

    /** Whole days left to decide. Zero on the last day, never negative. */
    public static function daysLeft(JobFairEvent $event): int
    {
        return max(0, (int) now()->startOfDay()
            ->diffInDays(self::closesOn($event)->startOfDay(), false));
    }
}
