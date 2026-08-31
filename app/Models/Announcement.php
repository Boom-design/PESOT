<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $primaryKey = 'announcements_id';

    protected $fillable = [
        'jobseeker_id', 'employer_id', 'staff_id',
        'type', 'title', 'message', 'is_read',
        'reference_type', 'reference_id',
        'sms_status', 'sms_sent_at', 'sms_error',
    ];

    protected $casts = [
        'is_read'     => 'boolean',
        'sms_sent_at' => 'datetime',
    ];

    public function jobseeker()
    {
        return $this->belongsTo(JobseekerRegistration::class, 'jobseeker_id');
    }

    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'employer_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Where a staff bell notification should land when it is clicked.
     *
     * The bell dropdown and the notifications page both need this and had
     * drifted apart: the dropdown knew about employer inactivity and the page
     * did not, the page knew about jobseeker notices and the dropdown did not.
     * The same notice therefore opened two different screens depending on
     * which of the two the staff clicked it from. One method, one answer.
     */
    public function staffLinkUrl(): string
    {
        return match ($this->reference_type) {
            'employer_requirement'   => route('staff.requirements.view', $this->reference_id),
            'employer_registration'  => route('staff.employers', ['tab' => 'pre']),
            'employer_inactivity'    => $this->employerInactivityUrl(),
            'jobseeker_registration' => route('staff.registrations.view', $this->reference_id),
            'jobseeker_notice'       => route('staff.registrations'),
            'job'                    => route('staff.jobs'),
            'inhouse_schedule'       => route('staff.inhouse'),
            'job_fair'               => route('staff.jobfair.events'),
            default                  => route('staff.notifications.index'),
        };
    }

    /**
     * An inactivity notice names one company, so the link opens the tab that
     * company is actually on and marks its row.
     *
     * This used to point at the Inactive tab for every inactivity notice. That
     * was right while the sweep switched accounts off by itself. It no longer
     * does — the account the desk is being asked to decide on is still in
     * Approved Employers — so the link landed on a list the company was not in
     * and the staff had to go and find it.
     */
    private function employerInactivityUrl(): string
    {
        $employer = EmployerNsrpRegistration::find($this->reference_id);

        return route('staff.employers', [
            'tab'       => $employer && $employer->dormant_at ? 'dormant' : 'approved',
            'highlight' => $this->reference_id,
        ]);
    }

    // ── Helpers — parehas ra ka signature sa daan, pero karon 1 row per recipient sa parehas nga table ──
    public static function sendToJobseekers(array $data, $jobseekerIds)
    {
        foreach ((array) (is_iterable($jobseekerIds) ? $jobseekerIds->toArray() ?? $jobseekerIds : [$jobseekerIds]) as $id) {
            self::create(array_merge($data, ['jobseeker_id' => $id]));
        }
    }

    public static function sendToEmployers(array $data, $employerIds)
    {
        foreach ((array) (is_iterable($employerIds) ? $employerIds->toArray() ?? $employerIds : [$employerIds]) as $id) {
            self::create(array_merge($data, ['employer_id' => $id]));
        }
    }

    public static function sendToStaff(array $data, $staffIds)
    {
        foreach ((array) (is_iterable($staffIds) ? $staffIds->toArray() ?? $staffIds : [$staffIds]) as $id) {
            self::create(array_merge($data, ['staff_id' => $id]));
        }
    }
}