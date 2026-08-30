<?php

namespace App\Support;

use App\Models\Job;
use App\Models\JobActivityLog;
use App\Models\User;

/**
 * Writes the history line for a posting.
 *
 * A posting is not fixed once it is live. The employer can start accepting PWD
 * applicants, add slots, move the deadline, rewrite what the work is — and can
 * report people they hired outside PESO. Every one of those changes what an
 * applicant was measured against, so every one of them is written down here and
 * shown on the posting.
 *
 * Only the answers that change who qualifies or how many are still wanted are
 * tracked. A typo fixed in the location is not history.
 */
class JobChangeLog
{
    /** Column => how it reads on the page. */
    public const TRACKED = [
        'title'                => 'Job title',
        'slots'                => 'Slots',
        'deadline'             => 'Application deadline',
        'salary'               => 'Salary',
        'type'                 => 'Employment type',
        'location'             => 'Location',
        'description'          => 'Job description',
        'industry_group'       => 'Industry',
        'accepts_disability'   => 'Accepts PWD applicants',
        'disability_types'     => 'Disability types accepted',
        'experience_months'    => 'Experience required (months)',
        'education_required'   => 'Education required',
        'course_major'         => 'Course / major',
        'license'              => 'Licence',
        'eligibility'          => 'Eligibility',
        'certification'        => 'Certification',
        'language'             => 'Language',
        'religion'             => 'Religion',
        'civil_status'         => 'Civil status',
        'sex_preference'       => 'Sex preference',
        'preferred_residence'  => 'Preferred residence',
        'other_qualifications' => 'Other qualifications',
    ];

    /** The values worth watching, read off a posting before it is written to. */
    public static function snapshot(Job $job): array
    {
        $snapshot = [];

        foreach (array_keys(self::TRACKED) as $field) {
            $snapshot[$field] = self::readable($job->{$field});
        }

        return $snapshot;
    }

    /**
     * Compare a snapshot against the posting as it now stands and, if anything
     * moved, write one row for the whole edit. One row, not one per field: the
     * employer pressed Save once, and a list of nine lines for one Save reads
     * like nine visits.
     */
    public static function recordEdit(Job $job, array $before, ?User $actor): ?JobActivityLog
    {
        $after   = self::snapshot($job->fresh());
        $changes = [];

        foreach ($before as $field => $wasValue) {
            $nowValue = $after[$field] ?? null;

            if ($wasValue === $nowValue) continue;

            $changes[$field] = [
                'label' => self::TRACKED[$field] ?? $field,
                'from'  => $wasValue,
                'to'    => $nowValue,
            ];
        }

        if ($changes === []) return null;

        $labels = collect($changes)->pluck('label');

        return self::write($job, $actor, 'qualifications_updated', self::sentence($labels), $changes);
    }

    /** The employer reporting people they took on without going through PESO. */
    public static function recordExternalHires(Job $job, int $from, int $to, ?User $actor): ?JobActivityLog
    {
        if ($from === $to) return null;

        $summary = $to === 0
            ? 'Cleared the hires made outside PESO (was ' . $from . ').'
            : $to . ' hire(s) made outside PESO were reported'
              . ($from > 0 ? ' (was ' . $from . ')' : '')
              . '. Those fill slots on this posting but are not PESO placements.';

        return self::write($job, $actor, 'external_hires_recorded', $summary, [
            'external_hires' => ['label' => 'Hires outside PESO', 'from' => (string) $from, 'to' => (string) $to],
        ]);
    }

    /** The whole history of one posting, newest first. */
    public static function forJob(Job $job)
    {
        return JobActivityLog::whereIn('job_id', $job->groupJobIds())
            ->latest()
            ->get();
    }

    // ───────────────────────────────
    private static function write(Job $job, ?User $actor, string $action, string $summary, array $changes): JobActivityLog
    {
        return JobActivityLog::create([
            // Against the group leader, so a position posted through three
            // channels keeps one history rather than three partial ones.
            'job_id'        => $job->group_key,
            'actor_user_id' => $actor?->users_id,
            'actor_name'    => $actor?->name,
            'action'        => $action,
            'summary'       => $summary,
            'changes'       => $changes,
        ]);
    }

    private static function sentence($labels): string
    {
        if ($labels->count() === 1) {
            return 'Updated ' . lcfirst($labels->first()) . '.';
        }

        return 'Updated ' . $labels->map(fn($l) => lcfirst($l))->join(', ', ' and ') . '.';
    }

    /** Arrays and nulls compared and printed as one plain string. */
    private static function readable($value): string
    {
        if ($value === null) return '';
        if (is_bool($value)) return $value ? 'yes' : 'no';
        if (is_array($value)) return implode(', ', $value);
        if ($value instanceof \DateTimeInterface) return $value->format('Y-m-d');

        return trim((string) $value);
    }
}
