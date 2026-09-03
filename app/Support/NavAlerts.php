<?php

namespace App\Support;

use App\Models\Application;
use App\Models\EmployerRequirement;
use App\Models\InhouseSchedule;
use App\Models\Job;
use App\Models\JobFairParticipant;
use App\Models\Staff;

/**
 * Counts behind the red dot on a sidebar item.
 *
 * One rule for every portal: a dot means *this user has something to act on
 * here*, not merely that the page has new data. A dot that cannot be cleared
 * by doing anything is noise, and after a week nobody looks at any of them.
 *
 * Each method returns [navKey => count]; a key is only present when its count
 * is greater than zero, so a layout can simply check isset().
 */
class NavAlerts
{
    /** Drop the zeroes so the views never have to test for them. */
    private static function pruned(array $counts): array
    {
        return array_filter($counts, fn($n) => $n > 0);
    }

    // ── EMPLOYER ──
    public static function forCompany(?int $employerNsrpId): array
    {
        if (!$employerNsrpId) {
            return [];
        }

        $requirement = EmployerRequirement::where('user_id', $employerNsrpId)->first();

        return self::pruned([
            // Job fair invitations waiting for a Confirm or Decline. This is the
            // one the office asked for by name.
            'active_job_vacancy' => JobFairParticipant::where('employer_id', $employerNsrpId)
                ->where('confirmation_status', 'pending')
                ->count(),

            // Walay dot para sa gitangtang nga posting: wala nay nav item para
            // niini, ug ang rason anaa na sa notification mismo. Ang query gikuha
            // aron dili na siya modagan kada page load.

            // Only rejected/expired documents count. "Not submitted yet" already
            // has its own permanent "Required" flag beside the same item.
            'requirements' => in_array($requirement?->status, ['rejected', 'expired'], true) ? 1 : 0,
        ]);
    }

    // ── JOBSEEKER ──
    public static function forJobseeker(?int $registrationId): array
    {
        if (!$registrationId) {
            return [];
        }

        // Applications still waiting for the jobseeker to say whether they are
        // coming. Scoped to postings that are still active — a prompt for an
        // interview that has already passed cannot be acted on.
        $pendingParticipation = Application::where('jobseeker_id', $registrationId)
            ->where(function ($q) {
                $q->where('inhouse_participation', 'pending')
                  ->orWhere('company_interview_participation', 'pending');
            })
            ->whereHas('job', fn($q) => $q->active())
            ->count();

        // Job fair attendance the office asked about and the jobseeker has not
        // answered. The layout also raises a modal for this, but the modal can
        // be dismissed and the question stays open.
        $pendingAttendance = \App\Models\JobFairRegistration::where('user_id', $registrationId)
            ->whereNull('is_attended')
            ->whereNotNull('attendance_notified_at')
            ->count();

        return self::pruned([
            'job_vacancies' => $pendingParticipation,
            'schedules'     => $pendingAttendance,
        ]);
    }

    // ── PESO STAFF ── (the menu differs per role, so the keys do too)
    public static function forStaff(?Staff $staff): array
    {
        if (!$staff) {
            return [];
        }

        $role = $staff->staff_role;

        // lra and job_vacancy handle local employers, sra handles overseas.
        $overseas = $role === 'sra';
        $scopeEmployer = fn($q) => $q->whereHas('employer', fn($n) => $n->where('is_overseas', $overseas));
        $scopeCompany  = fn($q) => $q->whereHas('company',  fn($n) => $n->where('is_overseas', $overseas));

        if ($role === 'lra' || $role === 'sra') {
            $pendingSchedules = InhouseSchedule::where('status', 'pending')->where($scopeEmployer)->count();
            $pendingInhouseJobs = Job::where('schedule_type', 'inhouse')
                ->where('posting_status', 'pending')
                ->where($scopeCompany)
                ->count();

            // ── Ang ahensya nga mitubag ug oo ug naghulat sa pagpili sa SRA.
            // ──
            // ── Ang tubag sa ahensya walay pahibalo nga makita sa listahan —
            // ── mohilom lang siya sa Job Fair nga tab hangtod may moabli. Ang
            // ── tuldok mao ang nagsulti nga naay tawo nga naghulat, ug
            // ── mawala siya kung nadesisyonan na. Overseas ra: ang lokal
            // ── walay ing-ani nga lakang. ──
            $awaitingSelection = $overseas
                ? JobFairParticipant::where('confirmation_status', 'accepted')
                    ->where($scopeEmployer)
                    ->whereHas('jobFair', fn($e) => $e->whereDate('event_date', '>=', today()))
                    ->count()
                : 0;

            return self::pruned([
                'employers'      => EmployerRequirement::where('status', 'pending')->where($scopeEmployer)->count(),
                'job_activities' => $pendingSchedules + $pendingInhouseJobs + $awaitingSelection,
            ]);
        }

        if ($role === 'job_vacancy') {
            return self::pruned([
                'employers' => EmployerRequirement::where('status', 'pending')
                    ->whereHas('employer', fn($n) => $n->where('is_overseas', false))
                    ->count(),

                'job_activities' => Job::where('posting_status', 'pending')
                    ->whereHas('company', fn($n) => $n->where('is_overseas', false))
                    ->where(function ($q) {
                        $q->whereNull('schedule_type')->orWhere('schedule_type', 'company_interview');
                    })
                    ->count(),
            ]);
        }

        if ($role === 'job_fair') {
            // Approved but still closed: staff has to open these before
            // jobseekers can see them at the event.
            //
            // Counted from the last time the desk opened Job Fair Vacancies.
            // Before that mark existed the number stayed lit after the page had
            // been read, because only "Post All Job Vacancies" emptied the list
            // and the desk chooses when to press it — normally five days before
            // the fair. Opening the page answers the question the number asks;
            // a posting approved afterwards lights it again.
            $seenAt = $staff->postings_seen_at;

            return self::pruned([
                'postings' => Job::where('schedule_type', 'job_fair')
                    ->where('posting_status', 'approved')
                    ->where('status', 'closed')
                    ->when($seenAt, fn($q) => $q->where('updated_at', '>', $seenAt))
                    ->count(),
            ]);
        }

        return [];
    }

    // ── ADMIN ──
    public static function forAdmin(): array
    {
        // The admin is the one portal where the dot does not mean "act on
        // this". Nothing here is the admin's to approve; they are reading over
        // the desks' shoulders. So the count is what they have not looked at
        // yet, and opening the page is what clears it — see App\Support\AdminInbox.
        //
        // It used to count pending in-house requests and pending postings,
        // which the admin cannot clear by any action, so the number sat there
        // until an LRA got round to it.
        return self::pruned([
            'job_activities' => AdminInbox::jobActivityTotal(),
            'registrations'  => AdminInbox::registrationCount(),
        ]);
    }
}
