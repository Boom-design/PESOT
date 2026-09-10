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
            'job'                    => $this->jobNoticeUrl(),
            'inhouse_schedule'       => route('staff.inhouse'),
            'job_fair'               => route('staff.jobfair.events'),
            // Ang pagpili sa SRA. Dili ni mahimong 'job_fair': kana nga route
            // Job Fair desk ra ang makasulod, ug ang SRA nga mo-klik mabalibad
            // ngadto sa login. Ang iyang trabaho naa sa Invite nga panel sa
            // Job Fair nga tab.
            'job_fair_selection'     => route('staff.inhouse.jobfair', ['panel' => 'invite']),
            default                  => route('staff.notifications.index'),
        };
    }

    /**
     * A posting notice opens the tab that posting is listed on.
     *
     * Every one of them used to land on staff.jobs, which is the Company
     * Interview list. A job fair posting is not on it, so the desk was dropped
     * on a page that did not contain the thing it had just been told about,
     * with no hint of where to look.
     *
     * The tab is named, never left to the page's own default. Bare staff.jobs
     * is the Company Interview list for Job Vacancy staff but the In-house one
     * for SRA — jobVacancies() reads request('type', 'inhouse') for that role —
     * so an SRA notice about a company interview posting opened a tab the
     * posting was not on. Saying which tab makes the link mean the same thing
     * at every desk.
     */
    private function jobNoticeUrl(): string
    {
        $job = Job::find($this->reference_id);

        if ($job?->schedule_type === 'job_fair') {
            return route('staff.inhouse.jobfair');
        }

        if ($job?->schedule_type === 'inhouse') {
            return route('staff.jobs', ['type' => 'inhouse']);
        }

        // An overseas company interview posting waits for the SRA before
        // jobseekers see it, and the Approve and Reject buttons live on the
        // Pending Company Interview tab, not on the Company Interview list —
        // that list is the record of what has already been solicited. The
        // notice has to open the tab the desk can act on, or it is an errand
        // rather than a link.
        if ($job?->posting_status === 'pending') {
            return route('staff.jobs', ['type' => 'company_interview_pending']);
        }

        return route('staff.jobs', ['type' => 'company_interview']);
    }

    /**
     * An inactivity notice names one company, so the link opens the tab that
     * company is actually on and marks its row.
     *
     * This used to point at the Inactive tab for every inactivity notice. That
     * was right while the sweep switched accounts off by itself. It no longer
     * does — the account the desk is being asked to decide on is still in
     * Registered Employer — so the link landed on a list the company was not in
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
    /**
     * One id, a plain array of ids, or a collection of them — as a plain array.
     *
     * The three senders below used to work this out inline with
     * `is_iterable($ids) ? $ids->toArray() : [$ids]`. A plain PHP array is
     * iterable and has no toArray(), so every caller that passed one — such as
     * `[$participant->employer_id]` in decideOverseasSelection() — died on
     * "Call to a member function toArray() on array". The `?? $ids` after it
     * could not help: the call had already thrown.
     *
     * Written once, so the same mistake cannot be made in three places.
     */
    private static function recipientIds($ids): array
    {
        if ($ids instanceof \Illuminate\Support\Collection) {
            return $ids->all();
        }

        if (is_array($ids)) {
            return $ids;
        }

        if (is_iterable($ids)) {
            return iterator_to_array($ids);
        }

        return $ids === null ? [] : [$ids];
    }

    public static function sendToJobseekers(array $data, $jobseekerIds)
    {
        foreach (self::recipientIds($jobseekerIds) as $id) {
            self::create(array_merge($data, ['jobseeker_id' => $id]));
        }
    }

    public static function sendToEmployers(array $data, $employerIds)
    {
        foreach (self::recipientIds($employerIds) as $id) {
            self::create(array_merge($data, ['employer_id' => $id]));
        }
    }

    public static function sendToStaff(array $data, $staffIds)
    {
        foreach (self::recipientIds($staffIds) as $id) {
            self::create(array_merge($data, ['staff_id' => $id]));
        }
    }
}