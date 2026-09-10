<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Job;
use App\Models\JobseekerRegistration;

/**
 * Telling jobseekers a vacancy is live.
 *
 * There were three copies of this — the staff approval path, the job fair
 * opening command, and now the employer posting directly. One copy so the
 * wording and the matching rule cannot drift.
 *
 * Only call it when the posting is genuinely clickable. A notice that leads to
 * a posting the jobseeker cannot open is worse than no notice.
 */
class JobPostingNotice
{
    /**
     * Notify every approved jobseeker that this vacancy is live.
     *
     * Whoever listed the exact job title among their preferred occupations gets
     * the personal wording; everyone else gets the general one.
     *
     * A jobseeker only hears about the kind of work they registered for.
     * `jobseeker_nsrp_registrations.type` is local, overseas or both, and it is
     * the first question the NSRP form asks. Until now nothing read it here, so
     * an agency posting a carpenter for Riyadh notified a jobseeker who had
     * said plainly that they were looking for work at home — and the desk was
     * then told, on its own bell, that one jobseeker had been reached.
     *
     * A registration with no NSRP row yet counts as local. That is what the
     * column itself defaults to, and a jobseeker who has not said where they
     * want to work has not said "abroad".
     */
    /**
     * The NSRP classifications this vacancy is allowed to reach.
     *
     * Public because the job fair text uses the same rule. One copy, so an
     * overseas vacancy cannot reach a local-only jobseeker down one path while
     * being correctly held back on another.
     */
    public static function wantedTypesFor(Job $job): array
    {
        return optional($job->company)->is_overseas
            ? ['overseas', 'both']
            : ['local', 'both'];
    }

    public static function announce(Job $job): void
    {
        $matched   = collect();
        $unmatched = collect();
        $title     = strtolower($job->title);

        $wantedTypes = self::wantedTypesFor($job);

        $registrations = JobseekerRegistration::whereHas('user', fn($q) => $q->where('status', 'approved'))
            ->with('nsrp')
            ->get();

        foreach ($registrations as $registration) {
            if (!in_array($registration->nsrp->type ?? 'local', $wantedTypes, true)) {
                continue;
            }

            $preferred = $registration->nsrp->preferred_occupations ?? [];

            $isMatch = false;
            foreach ($preferred as $occupation) {
                if (strtolower(trim($occupation)) === $title) {
                    $isMatch = true;
                    break;
                }
            }

            ($isMatch ? $matched : $unmatched)->push($registration->jobseeker_registrations_id);
        }

        $company = $job->company->company_name ?? 'an employer';

        if ($matched->isNotEmpty()) {
            Announcement::sendToJobseekers([
                'type'           => 'job_match',
                'title'          => 'Matching Job Vacancy Found! 💼',
                'message'        => 'A job vacancy matching your preferred position "' . $job->title
                                    . '" from ' . $company . ' is now available. Would you like to apply?',
                'reference_type' => 'job',
                'reference_id'   => $job->job_qualifications_id,
            ], $matched);
        }

        if ($unmatched->isNotEmpty()) {
            Announcement::sendToJobseekers([
                'type'           => 'job_posted',
                'title'          => 'New Job Vacancy Posted 💼',
                'message'        => 'A new job vacancy "' . $job->title . '" from ' . $company
                                    . ' is now available!',
                'reference_type' => 'job',
                'reference_id'   => $job->job_qualifications_id,
            ], $unmatched);
        }

        self::tellTheDesk($job, $matched->count(), $unmatched->count());
    }

    /**
     * Tell the desk that answers for this employer how far the notice reached.
     *
     * PESO LRA, 2026-08-26: the desk is asked who the office informed about a
     * vacancy, and until now the system could send a thousand notices without
     * anyone at PESO seeing that it had. The counts are here because they are
     * what the office is asked for; the names are on the Registrations page,
     * which is where this notice points.
     *
     * Matched and unmatched are kept apart on purpose. "Matched" means the
     * jobseeker named this exact job title as a preferred occupation, and that
     * is the number the office cares about — the rest is a general
     * announcement.
     */
    private static function tellTheDesk(Job $job, int $matched, int $unmatched): void
    {
        $total = $matched + $unmatched;
        if ($total === 0) {
            return;
        }

        $isOverseas = (bool) optional($job->company)->is_overseas;
        $staffIds   = \App\Models\Staff::where('staff_role', $isOverseas ? 'sra' : 'lra')->pluck('staff_id');

        if ($staffIds->isEmpty()) {
            return;
        }

        Announcement::sendToStaff([
            'type'           => 'jobseekers_notified',
            'title'          => 'Jobseekers Notified 📣',
            'message'        => $total . ' jobseeker(s) were notified about "' . $job->title . '" from '
                . (optional($job->company)->company_name ?? 'an employer') . ' — '
                . $matched . ' matched it to a preferred occupation, ' . $unmatched . ' received the general notice.',
            'reference_type' => 'jobseeker_notice',
            'reference_id'   => $job->job_qualifications_id,
        ], $staffIds);
    }

    /**
     * The state a brand-new posting starts in.
     *
     * PESO no longer reviews an ordinary posting before it goes public: an
     * employer whose requirements are approved has already been checked, and
     * making them wait again only delays the vacancy. Staff keep the power to
     * take a posting down afterwards — see StaffWebController::rejectJob.
     *
     * Three exceptions.
     *
     * OVERSEAS COMPANY INTERVIEW. PESO SRA, 2026-09-05: an overseas agency
     * interviewing for a job abroad is not the same as a local employer
     * interviewing for a job down the road. The job order, the principal
     * employer and the deployment country all sit behind that posting, and the
     * SRA is the desk answerable for them — so it reads the posting before
     * jobseekers do. A local company interview still goes live on submit.
     *
     * It starts CLOSED for the same reason in-house does: a rejection has to
     * be able to stop the posting before anyone applies to it.
     *
     * IN-HOUSE. LRA staff, 2026-08-23: an in-house interview is held at the
     * PESO Office, and the office does not take every company — one offering a
     * couple of vacancies can occupy a whole day that two other employers
     * wanted. So this kind goes back to being reviewed, by LRA for local
     * employers and SRA for overseas ones.
     *
     * It starts CLOSED as well as pending. A rejection has to be able to stop
     * the interview from happening at all, and it cannot do that once
     * jobseekers have seen the vacancy and applied to it — the people a late
     * reversal would hurt are the applicants, who had no part in it.
     *
     * JOB FAIR. Approved on arrival but closed until the fair is near, so
     * jobseekers are not shown vacancies they can only act on weeks away.
     */
    public static function initialState(string $scheduleType, bool $isOverseas = false): array
    {
        if ($scheduleType === 'inhouse') {
            return ['posting_status' => 'pending', 'status' => 'closed'];
        }

        if ($scheduleType !== 'job_fair') {
            return $isOverseas
                ? ['posting_status' => 'pending', 'status' => 'closed']
                : ['posting_status' => 'approved', 'status' => 'open'];
        }


        return ['posting_status' => 'pending', 'status' => 'closed'];
    }

    /**
     * Why a posting of this kind is not visible yet, in words the employer can
     * act on. Null when it goes live immediately.
     */
    public static function pendingNote(string $scheduleType, bool $isOverseas = false): ?string
    {
        return match ($scheduleType) {
            'inhouse'  => 'It is waiting for PESO to approve the in-house schedule. '
                          . 'Jobseekers will see it once the office accepts the date.',
            'job_fair' => 'It is waiting for PESO to accept it into a job fair. '
                          . 'Once the office takes it in, ' . lcfirst(JobFairPostingWindow::liveNote()),
            // Named the desk, not "PESO". An overseas agency deals with the SRA
            // and nobody else, so a note that says only "PESO" sends it asking
            // at the wrong window.
            default    => $isOverseas
                          ? 'It is waiting for SRA staff to review it. '
                            . 'Jobseekers will see it once the desk approves the posting.'
                          : null,
        };
    }

    /** Whether a posting created with initialState() is visible right away. */
    public static function goesLive(string $scheduleType, bool $isOverseas = false): bool
    {
        return self::initialState($scheduleType, $isOverseas)['status'] === 'open';
    }
}
