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

        // A fair the jobseeker can join and has not joined.
        //
        // The attendance question above only exists after they have joined, so
        // on its own it leaves the first and larger question uncounted: the
        // office announced a fair, told them to open PESO Events and join it,
        // and nothing on the sidebar said there was anything to open.
        //
        // Same test as JobseekerWebController::schedules() uses to decide which
        // fairs the page shows, and the same threshold as the SMS gate — a
        // number for a fair that is not on the page could never be cleared.
        $joinedFairIds = \App\Models\JobFairRegistration::where('user_id', $registrationId)
            ->pluck('job_fair_id');

        // Fairs announced since the jobseeker last opened the Job Fair tab.
        // Reading the tab clears the number; the next fair raises it again.
        $seenAt = \App\Models\JobseekerRegistration::where('jobseeker_registrations_id', $registrationId)
            ->value('job_fair_seen_at');

        $openFairs = \App\Models\JobFairEvent::where('status', '!=', 'completed')
            ->whereNotIn('job_fair_events_id', $joinedFairIds)
            ->when($seenAt, fn($q) => $q->where(fn($w) =>
                $w->where('jobseekers_invited_at', '>', $seenAt)
                  ->orWhere('created_at', '>', $seenAt)))
            ->withCount(['participants as confirmed_count' => fn($q) =>
                $q->where('confirmation_status', 'confirmed')])
            ->having('confirmed_count', '>=', JobFairAudience::threshold())
            ->get()
            ->count();

        return self::pruned([
            'job_vacancies' => $pendingParticipation,
            'schedules'     => $openFairs,
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

        if ($role === 'lra' || $role === 'sra' || $role === 'job_vacancy') {
            // Ang numero sa sidebar kay ang sumada sa mga tab sa ilawom niya.
            // Usa ra ka lugar ang nag-ihap, mao nga dili sila magkalahi.
            return self::pruned([
                'employers' => EmployerRequirement::where('status', 'pending')
                    ->where($scopeEmployer)
                    ->count(),

                'job_activities' => array_sum(self::staffJobActivityCounts($staff)),
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

    /**
     * The Manage Job Activities number, split across the tabs that explain it.
     *
     * The keys are the tabs in partials/staff-activity-tabs, so a desk that is
     * told "2" can press the item and see which two tabs carry them. The
     * sidebar total is the sum of these, worked out here and nowhere else.
     *
     * A role that has no such tab gets a zero for it: the Job Vacancy desk does
     * not hold the PESO Office calendar, and only the SRA picks which overseas
     * agency is brought to a fair.
     */
    public static function staffJobActivityCounts(?Staff $staff): array
    {
        if (!$staff) {
            return [];
        }

        $role     = $staff->staff_role;
        $overseas = $role === 'sra';

        if (!in_array($role, ['lra', 'sra', 'job_vacancy'], true)) {
            return [];
        }

        $scopeEmployer = fn($q) => $q->whereHas('employer', fn($n) => $n->where('is_overseas', $overseas));
        $scopeCompany  = fn($q) => $q->whereHas('company',  fn($n) => $n->where('is_overseas', $overseas));

        $ownsTheCalendar = $role !== 'job_vacancy';

        return self::pruned([
            // Pending In-house Schedule — ang hangyo nga wala pa nadawat.
            //
            // Duha ka tinubdan ang gilista sa staff.inhouse: ang InhouseSchedule
            // nga hangyo nga walay posting, ug ang in-house nga posting nga
            // pending pa. Usa ra ka page, mao nga usa ra ka numero — kung
            // gibulag sila, ang usa ka numero mapadulong sa tab nga dili siya
            // makita didto.
            'inhouse_schedule' => $ownsTheCalendar
                ? InhouseSchedule::where('status', 'pending')->where($scopeEmployer)->count()
                    + Job::where('schedule_type', 'inhouse')
                        ->where('posting_status', 'pending')
                        ->where($scopeCompany)
                        ->count()
                : 0,

            // Pending Company Interview — posting_status = 'pending'. Ang lokal
            // buhi dayon sa pag-post, mao nga ang overseas ra ang makasulod.
            'company_interview_pending' => Job::where('posting_status', 'pending')
                ->where(function ($q) {
                    $q->where('schedule_type', 'company_interview')
                      ->orWhereNull('schedule_type');
                })
                ->where($scopeCompany)
                ->count(),

            // Ang In-house Job Vacancy nga tab walay numero. Nagbasa siya ug
            // posting_status = 'approved' — nadesisyunan na ang tanan didto,
            // mao nga walay naghulat sa desk. Parehas sa Company Interview nga
            // tab, nga wala pud.

            // Job Fair — ang ahensya nga mitubag ug oo ug naghulat sa pagpili
            // sa SRA. Ang tubag walay pahibalo nga makita sa listahan; ang
            // numero mao ang nagsulti nga naay tawo nga naghulat.
            'job_fair' => $overseas
                ? JobFairParticipant::where('confirmation_status', 'accepted')
                    ->where($scopeEmployer)
                    ->whereHas('jobFair', fn($e) => $e->whereDate('event_date', '>=', today()))
                    ->count()
                : 0,
        ]);
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
