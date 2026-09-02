<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * The hours a job fair can start.
 *
 * PESO Job Fair staff, 2026-09-02: the office day is 8:00 AM to 5:00 PM, and
 * the time picker should not be able to say anything else. A `<input
 * type="time">` with min and max accepts 6 PM and only argues on save, so the
 * form offers a list instead — and the list is built here so the form, the
 * edit form and the validation rule cannot drift apart.
 */
class JobFairEventHours
{
    /** First and last time a fair may be set to start, as H:i. */
    public const EARLIEST = '08:00';
    public const LATEST   = '17:00';

    /** Half-hour steps: the office sets a fair on the hour or the half. */
    private const STEP_MINUTES = 30;

    /**
     * The selectable times, as ['08:00' => '8:00 AM', ...].
     *
     * An extra time is folded in when the caller passes one — an event saved
     * before this list existed may sit at 9:15, and the edit form must show
     * what the event actually is rather than quietly moving it.
     */
    public static function slots(?string $include = null): array
    {
        $slots  = [];
        $cursor = Carbon::createFromFormat('H:i', self::EARLIEST);
        $end    = Carbon::createFromFormat('H:i', self::LATEST);

        while ($cursor->lte($end)) {
            $slots[$cursor->format('H:i')] = $cursor->format('g:i A');
            $cursor->addMinutes(self::STEP_MINUTES);
        }

        if ($include && !isset($slots[$include])) {
            $slots[$include] = Carbon::createFromFormat('H:i', $include)->format('g:i A');
            ksort($slots);
        }

        return $slots;
    }
}
