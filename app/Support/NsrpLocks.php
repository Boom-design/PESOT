<?php

namespace App\Support;

use App\Models\JobseekerRegistration;

/**
 * What the jobseeker may no longer change on their own form.
 *
 * PESO, 2026-08-28: the NSRP form is a signed record, and a few of its answers
 * are the record. A name, a birthday and a sex do not change; a school already
 * finished cannot be unfinished. Letting them be retyped means the copy the
 * office holds and the copy on screen can disagree, and nobody can tell which
 * one the employer was sent.
 *
 * A field locks the moment it holds something, never before — a blank suffix
 * stays open because the jobseeker may simply not have reached it yet, and the
 * masters row stays open because somebody who has just finished a masters must
 * be able to add it. Once it is in, it is in.
 *
 * Both the form and the controller ask this class, so a disabled input and a
 * refused write can never disagree about what is locked. The form is only the
 * courtesy; the controller is the rule.
 */
class NsrpLocks
{
    /** Identity answers that stop being editable once answered. */
    public const IDENTITY_FIELDS = ['first_name', 'suffix', 'date_of_birth', 'sex'];

    /** The one schooling row a jobseeker can still add after the first save. */
    public const LATE_EDUCATION_LEVEL = 'Graduate Studies/Post-graduate/Masters';

    public static function identityLocked(?JobseekerRegistration $registration, string $field): bool
    {
        return $registration !== null && filled($registration->{$field});
    }

    /** The stored education map, whatever shape the column came back in. */
    public static function storedEducation($nsrp): array
    {
        $education = $nsrp->education ?? null;

        if (is_array($education)) return $education;

        return json_decode((string) $education, true) ?: [];
    }

    /**
     * A level is locked once the jobseeker has actually answered it.
     *
     * "N/A" does not count: the form posts it as a hidden value for Elementary
     * and Junior High, where there is no course to name, so every one of those
     * rows would otherwise arrive pre-locked and empty.
     */
    public static function educationLocked(array $education, string $level): bool
    {
        foreach ($education[$level] ?? [] as $value) {
            if (filled($value) && strcasecmp(trim((string) $value), 'N/A') !== 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * The education to save: whatever was posted, except that a level already
     * answered keeps the answer it already has.
     */
    public static function mergeEducation(array $stored, array $submitted): array
    {
        foreach ($stored as $level => $row) {
            if (self::educationLocked($stored, $level)) {
                $submitted[$level] = $row;
            }
        }

        return $submitted;
    }
}
