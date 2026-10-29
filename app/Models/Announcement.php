<?php

namespace App\Models;

use App\Models\EmployerRequirement;
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
            'employer_requirement'   => $this->employerRequirementUrl(),
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
     * Where a notice about an employer's folder lands.
     *
     * PESO, 2026-10-29: it opened the standalone review page. The desk does
     * not read a folder there — it reads it from the Employers list, in the
     * window that opens on the employer's own row, where the rest of that
     * employer's record is. The bell lands there now, on the right tab and
     * the right page of it, with that window already open.
     */
    private function employerRequirementUrl(): string
    {
        $requirement = EmployerRequirement::find($this->reference_id);

        if (!$requirement) {
            return route('staff.employers', ['tab' => 'pre']);
        }

        // A folder still waiting sits on the Pre-registered tab; one already
        // decided has moved to Approved. Landing on the wrong tab shows a
        // list the employer is not in.
        return route('staff.employers', [
            'tab'       => $requirement->status === 'pending' ? 'pre' : 'approved',
            // The requirement's user_id is the employer's NSRP registration
            // id — the id the list and its modal are keyed by.
            'highlight' => $requirement->user_id,
        ]);
    }

    /**
     * Where an admin bell notification should land when it is clicked.
     *
     * Both admin views used to work this out inline, with the same expression
     * in each:
     *
     *     $notif->reference_type === 'registration'
     *         ? route('admin.registration.view', $notif->reference_id)
     *         : route('admin.dashboard')
     *
     * Nothing in the system writes `reference_type = 'registration'` — the
     * jobseeker one is `jobseeker_registration` — so the test never passed and
     * every notice in the admin's bell, whatever it was about, opened the
     * dashboard. The admin was told something had happened and then shown the
     * page they were already on.
     *
     * The admin sees every notice in the office, not only the ones addressed to
     * them, so this has to answer for the employer and vacancy references too.
     * Written here beside staffLinkUrl() for the reason that one gives: two
     * copies of a link map drift apart.
     */
    public function adminLinkUrl(): string
    {
        return match ($this->reference_type) {
            // An account with no NSRP form yet has no registration row to open,
            // so the reference is the users row and the link is the list that
            // shows them — where the amber "NSRP form not filled up yet" badge
            // already marks who they are.
            'jobseeker_account'      => route('admin.users.manage', [
                                            'tab'       => 'jobseeker',
                                            'highlight' => $this->reference_id,
                                        ]),
            'jobseeker_registration' => route('admin.registration.view', $this->reference_id),
            'jobseeker_notice'       => route('admin.registrations'),

            // The employer references point at an employer or requirement row,
            // not at a users row, so there is no id to highlight by. The tab is
            // as far as the link can honestly go.
            'employer_registration',
            'employer_requirement',
            'employer_inactivity'    => route('admin.users.manage', ['tab' => 'company']),

            'job'                    => $this->adminJobNoticeUrl(),
            'inhouse_schedule'       => route('admin.job.activities', ['tab' => 'inhouse']),
            'job_fair',
            'job_fair_selection',
            // Nothing writes this one any more — the rows carrying it are
            // historic, from when a jobseeker's attendance was confirmed by a
            // notice of its own. The admin bell reads every row in the table,
            // including the old ones, so it still needs an answer.
            'job_fair_registration'  => route('admin.job.activities', ['tab' => 'jobfair']),

            default                  => route('admin.notifications.index'),
        };
    }

    /** A vacancy notice opens the Job Activities tab that vacancy is listed on. */
    private function adminJobNoticeUrl(): string
    {
        $tab = match (Job::find($this->reference_id)?->schedule_type) {
            'job_fair' => 'jobfair',
            'inhouse'  => 'inhouse',
            default    => 'companyinterview',
        };

        return route('admin.job.activities', ['tab' => $tab]);
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
            // The Job Fair desk has a page of its own for these, and cannot
            // open staff.inhouse.jobfair at all — that route is the LRA's
            // events table and the SRA's agency line-up. Sending them there
            // bounced them to their dashboard, which is the link failing
            // quietly rather than the desk being taken to the vacancy.
            return optional(optional(auth()->user())->staff)->staff_role === 'job_fair'
                ? route('staff.jobfair.postings')
                : route('staff.inhouse.jobfair');
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