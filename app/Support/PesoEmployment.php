<?php

namespace App\Support;

use App\Models\Application;
use App\Models\JobseekerNsrpRegistration;
use App\Models\JobseekerRegistration;
use App\Models\JobseekerWorkExperience;
use Carbon\Carbon;

/**
 * A job a jobseeker got through PESO.
 *
 * PESO CDO client, 2026-09-14: once an employer marks someone hired, the job
 * goes onto their NSRP form under VII. Work Experience, and they stop applying
 * for other vacancies. When the job ends they say they are looking for work
 * again, and applying opens back up.
 *
 * The record of that is the work experience row itself — one with a
 * job_matching_id and is_current set. Nothing else has to be kept in step with
 * it. A hire recorded before this existed has no such row, so it never blocks
 * anyone.
 */
class PesoEmployment
{
    /** The reasons a job ends, worded as the NSRP form words them. */
    public const END_REASONS = [
        'finished_contract' => 'Finished Contract',
        'resigned'          => 'Resigned',
        'terminated_local'  => 'Terminated/Laid off',
        'others'            => 'Others',
    ];

    private const EMPLOYMENT_STATUS = [
        'permanent'   => 'Permanent',
        'contractual' => 'Contractual',
        'part_time'   => 'Part-time',
    ];

    /** The employment fields on the NSRP form that a hire changes. */
    private const STATUS_FIELDS = [
        'employment_type', 'employed_sub_type', 'self_employed_specify',
        'months_looking', 'unemployed_reason', 'unemployed_other', 'terminated_abroad_country',
    ];

    /** The job this jobseeker currently holds through PESO, if any. */
    public static function current(?int $registrationId): ?JobseekerWorkExperience
    {
        if (!$registrationId) {
            return null;
        }

        return JobseekerWorkExperience::whereNotNull('job_matching_id')
            ->where('is_current', true)
            ->whereHas('nsrpRegistration', fn($q) => $q->where('jobseeker_registration_id', $registrationId))
            ->latest('jobseeker_work_experiences_id')
            ->first();
    }

    public static function currentForUser(?int $userId): ?JobseekerWorkExperience
    {
        if (!$userId) {
            return null;
        }

        return self::current(JobseekerRegistration::where('user_id', $userId)->value('jobseeker_registrations_id'));
    }

    /** The first month of the job, for the "since" line and the earliest end month. */
    public static function startMonth(JobseekerWorkExperience $employment): ?Carbon
    {
        try {
            return $employment->date_from ? Carbon::createFromFormat('!m/Y', $employment->date_from) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Bring the NSRP form in line with an application's status, right after
     * the employer changes it.
     *
     * Hired: the job is written onto Work Experience as the current job, and
     * the employment status on the form becomes Employed. Anything else: a row
     * written for this hire is taken back off, and if it was the job they held,
     * the employment status goes back to what it was before.
     */
    public static function syncHire(Application $application): void
    {
        $application->loadMissing('job.company');

        $nsrp = JobseekerNsrpRegistration::where('jobseeker_registration_id', $application->jobseeker_id)->first();
        if (!$nsrp || !$application->job) {
            return;
        }

        $row = JobseekerWorkExperience::where('job_matching_id', $application->job_matching_id)->first();

        if ($application->status !== 'hired') {
            if ($row) {
                $wasCurrent = $row->is_current;
                $before     = $row->status_before;
                $row->delete();

                if ($wasCurrent && is_array($before) && !self::current($application->jobseeker_id)) {
                    $nsrp->update(array_intersect_key($before, array_flip(self::STATUS_FIELDS)));
                }
            }
            return;
        }

        $start = $application->start_date ?? $application->hired_at ?? now();
        $job   = $application->job;

        $data = [
            'jobseeker_nsrp_registration_id' => $nsrp->jobseeker_nsrp_registrations_id,
            'company_name'      => $job->company->company_name ?? 'Employer',
            'position'          => $job->title,
            'industry'          => $job->location,
            'date_from'         => Carbon::parse($start)->format('m/Y'),
            'date_to'           => 'present',
            'is_current'        => true,
            'employment_status' => self::EMPLOYMENT_STATUS[$job->type] ?? null,
        ];

        if ($row) {
            $row->update($data);
        } else {
            JobseekerWorkExperience::create($data + [
                'job_matching_id' => $application->job_matching_id,
                'status_before'   => $nsrp->only(self::STATUS_FIELDS),
            ]);
        }

        $nsrp->update([
            'employment_type'           => 'employed',
            'employed_sub_type'         => 'wage_employed',
            'self_employed_specify'     => null,
            'months_looking'            => null,
            'unemployed_reason'         => null,
            'unemployed_other'          => null,
            'terminated_abroad_country' => null,
        ]);
    }

    /** The job ended: close it on Work Experience and mark them unemployed, looking again. */
    public static function end(JobseekerWorkExperience $employment, string $reason, Carbon $lastMonth, ?string $other = null): void
    {
        $employment->update([
            'is_current' => false,
            'date_to'    => $lastMonth->format('m/Y'),
        ]);

        $employment->nsrpRegistration?->update([
            'employment_type'   => 'unemployed',
            'employed_sub_type' => null,
            'months_looking'    => '0',
            'unemployed_reason' => $reason,
            'unemployed_other'  => $reason === 'others' ? $other : null,
        ]);
    }
}
