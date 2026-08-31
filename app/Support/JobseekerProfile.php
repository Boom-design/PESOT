<?php

namespace App\Support;

use App\Models\JobseekerNsrpRegistration;

/**
 * Reading the NSRP form's answers back as plain facts.
 *
 * The form stores schooling as a map of five levels, each with its own school
 * name and year. That is the right shape for a form and the wrong shape for a
 * question — and the office only ever asks one question of it: how far did
 * this person get. So the answer is worked out in one place, and the staff
 * list, the export and anything that comes later all read the same one.
 */
class JobseekerProfile
{
    /**
     * The five levels, highest first.
     *
     * The keys are exactly what the NSRP form stores; the values are what a
     * person reading a list wants to see. Do not reorder — the order is the
     * ranking, and highestEducation() walks it from the top.
     */
    public const EDUCATION_LEVELS = [
        'Graduate Studies/Post-graduate/Masters' => 'Graduate Studies',
        'Tertiary / College'                     => 'Tertiary / College',
        'Senior High School'                     => 'Senior High School',
        'Junior High School'                     => 'Junior High School',
        'Elementary'                             => 'Elementary',
    ];

    /**
     * How far this jobseeker got in school, as one of EDUCATION_LEVELS.
     *
     * A level counts as reached when a school was named for it. The year and
     * the course are left out of the test on purpose: somebody who wrote down
     * a college but never finished still reached college, and the office is
     * asking where they got to, not what they finished.
     *
     * Returns null when the form has no schooling on it at all.
     */
    public static function highestEducation(?JobseekerNsrpRegistration $nsrp): ?string
    {
        if (!$nsrp) {
            return null;
        }

        $education = is_array($nsrp->education)
            ? $nsrp->education
            : (json_decode($nsrp->education ?? '[]', true) ?: []);

        foreach (self::EDUCATION_LEVELS as $key => $label) {
            if (filled($education[$key]['school_name'] ?? null)) {
                return $label;
            }
        }

        return null;
    }
}
