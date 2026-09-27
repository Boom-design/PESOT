<?php

namespace App\Support;

/**
 * What an application's status is called on screen.
 *
 * PESO CDO, 2026-09-16: "Waiting" was read as "wait for the day" — a jobseeker
 * put on Waiting at a job fair thought they were being told to stay at the
 * fair. It never meant that. It means the employer has taken them forward and
 * something is still outstanding:
 *
 *   in-house / company interview — the employer wants them, and is waiting on
 *   the rest of their requirements;
 *   job fair — the employer is bringing them to a further interview, at the
 *   company or wherever the employer holds it.
 *
 * So the word on every screen is "On Process", with the reason spelled out
 * beside it. The stored value stays 'waiting': it is the same decision, and
 * renaming it would rewrite every application ever recorded.
 */
class ApplicationStatus
{
    public const ON_PROCESS = 'waiting';

    /** The badge word. */
    public static function label(?string $status): string
    {
        return match ($status) {
            'waiting'  => 'On Process',
            'rejected' => 'Not selected',
            null, ''   => 'Pending',
            default    => ucfirst($status),
        };
    }

    /**
     * The line under the badge: why they are on process.
     *
     * Empty for every other status — nothing to explain.
     */
    public static function note(?string $status, ?string $scheduleType = null): string
    {
        if ($status !== 'waiting') {
            return '';
        }

        return $scheduleType === 'job_fair'
            ? 'For further interview'
            : 'Complying with requirements';
    }

    /** Badge and note in one line, for a table cell that has room for neither. */
    public static function full(?string $status, ?string $scheduleType = null): string
    {
        $note = self::note($status, $scheduleType);

        return $note ? self::label($status) . ' — ' . $note : self::label($status);
    }
}
