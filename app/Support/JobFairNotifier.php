<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Application;
use App\Models\Job;
use App\Models\JobFairEmploymentRequest;
use App\Models\JobFairEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Telling jobseekers about a job fair vacancy they qualify for.
 *
 * ── WHY THIS IS NOT A BUTTON ANY MORE ──
 *
 * PESO Job Fair staff, 2026-10-23: the desk had to open every fair vacancy
 * and press Send on each one, twice — once for the applicants and once for
 * the people who qualify but have not applied. A fair carries dozens of
 * vacancies. The press is not a decision: the rules decide who is texted, and
 * the desk was only providing the finger.
 *
 * Worse, a missed press is invisible. Nothing says "this vacancy never went
 * out", so the people who would have heard simply did not, and nobody found
 * out until the fair was over.
 *
 * So it runs by itself, nightly, and this class holds the rules it follows.
 * Every guard the buttons had is still here:
 *
 *   · the fair must have reached its confirmed-employer gate, because a fair
 *     nobody has agreed to attend is not worth a text;
 *   · the vacancy must be open — a jobseeker cannot act on a posting that is
 *     still closed until the cutoff;
 *   · nobody is texted twice about the same vacancy, which is what the
 *     Announcement rows are for. A text costs money.
 *
 * Two audiences, two messages, two dedupe keys. An applicant already on the
 * list gets 'job_fair'; a person who qualifies and has not applied gets
 * 'job_fair_prospect' and a message that tells them to apply. The same person
 * may receive both over time — they are two different pieces of news — but
 * neither twice.
 */
class JobFairNotifier
{
    /** An applicant is worth a text from this match upward. */
    public const APPLICANT_THRESHOLD = 50;

    /** Which fair a vacancy was posted to; the newest request is the answer. */
    public static function eventOf(Job $job): ?JobFairEvent
    {
        $request = JobFairEmploymentRequest::where('job_id', $job->job_qualifications_id)
            ->latest('job_fair_employment_requests_id')
            ->first();

        return $request ? JobFairEvent::find($request->job_fair_id) : null;
    }

    /**
     * The vacancies worth looking at tonight.
     *
     * Open postings on a fair that has not happened yet and has met its gate.
     * A fair in the past cannot be attended, and a closed posting cannot be
     * applied to.
     */
    public static function dueVacancies(): Collection
    {
        $events = JobFairEvent::whereDate('event_date', '>=', today())
            ->where('status', '!=', 'completed')
            ->get()
            ->filter(fn($event) => JobFairAudience::gateMet($event));

        if ($events->isEmpty()) {
            return collect();
        }

        $jobIds = JobFairEmploymentRequest::whereIn('job_fair_id', $events->pluck('job_fair_events_id'))
            ->pluck('job_id');

        if ($jobIds->isEmpty()) {
            return collect();
        }

        return Job::with('company')
            ->whereIn('job_qualifications_id', $jobIds)
            ->where('schedule_type', 'job_fair')
            ->where('status', 'open')
            ->get();
    }

    /**
     * Send both notices for one vacancy.
     *
     * @return array{applicants:int, prospects:int, sent:int, failed:int}
     */
    public static function notifyVacancy(Job $job, JobFairEvent $event): array
    {
        $applicants = self::send(
            $job, $event,
            self::applicantRecipients($job),
            'job_fair',
            'Job Fair Vacancy For You 💼',
            self::applicantMessage($job, $event),
            self::applicantSmsText($job, $event)
        );

        $prospects = self::send(
            $job, $event,
            self::prospectRecipients($job),
            'job_fair_prospect',
            'You Qualify For This Job Fair Vacancy 💼',
            self::prospectMessage($job, $event),
            self::prospectSmsText($job, $event)
        );

        return [
            'applicants' => $applicants['people'],
            'prospects'  => $prospects['people'],
            'sent'       => $applicants['sent'] + $prospects['sent'],
            'failed'     => $applicants['failed'] + $prospects['failed'],
        ];
    }

    /**
     * One notice to one audience: an announcement each, then one text run.
     *
     * The announcement is written before the text is attempted, so a person
     * whose number fails still has the notice waiting for them on the site —
     * and the row is what stops them being texted about this vacancy again.
     *
     * @return array{people:int, sent:int, failed:int}
     */
    private static function send(
        Job $job,
        JobFairEvent $event,
        Collection $rows,
        string $type,
        string $title,
        string $message,
        string $smsText
    ): array {
        if ($rows->isEmpty()) {
            return ['people' => 0, 'sent' => 0, 'failed' => 0];
        }

        $breakdown = JobFairAudience::breakdown($rows);

        $created = [];
        foreach ($rows as $row) {
            $created[] = [
                'number'       => $row['number'],
                'announcement' => Announcement::create([
                    'type'           => $type,
                    'title'          => $title,
                    'message'        => $message,
                    // A jobseeker who taps it lands on the vacancy. It is also
                    // the mark that says they have been told about this one.
                    'reference_type' => 'job',
                    'reference_id'   => $job->job_qualifications_id,
                    'jobseeker_id'   => $row['id'],
                    'sms_status'     => 'pending',
                ]),
            ];
        }

        $result      = PhilSms::send(array_column($breakdown['sendable'], 'number'), $smsText);
        $now         = now();
        $sentNumbers = array_flip($result['sent']);

        foreach ($created as $entry) {
            $number       = $entry['number'];
            $announcement = $entry['announcement'];

            if ($number !== null && isset($sentNumbers[$number])) {
                $announcement->update([
                    'sms_status'  => 'sent',
                    'sms_sent_at' => $now,
                    'sms_error'   => null,
                ]);
            } elseif ($number !== null && isset($result['failed'][$number])) {
                $announcement->update([
                    'sms_status' => 'failed',
                    'sms_error'  => Str::limit($result['failed'][$number], 250),
                ]);
            } else {
                // No number, an unusable one, or opted out — the notice on the
                // site is still there, only the text is not.
                $announcement->update(['sms_status' => 'not_applicable']);
            }
        }

        return [
            'people' => $rows->count(),
            'sent'   => count($result['sent']),
            'failed' => count($result['failed']),
        ];
    }

    /**
     * What is still to go out for one vacancy, and what already has.
     *
     * The desk's page reads this. It has no button any more, so the numbers
     * are the whole of what it can say: how many have been told, and how many
     * the next nightly run will reach.
     *
     * @return array{
     *     applicants_waiting:int, applicants_told:int,
     *     prospects_waiting:int,  prospects_told:int
     * }
     */
    public static function pending(Job $job): array
    {
        $applicants = self::applicantRecipients($job);
        $prospects  = self::prospectRecipients($job);

        return [
            'applicants_waiting' => count(JobFairAudience::breakdown($applicants)['sendable']),
            'applicants_told'    => self::alreadyTold('job_fair', $job)->count(),
            'prospects_waiting'  => count(JobFairAudience::breakdown($prospects)['sendable']),
            'prospects_told'     => self::alreadyTold('job_fair_prospect', $job)->count(),
        ];
    }

    /** Applicants at or above the mark who have not been told about this one. */
    private static function applicantRecipients(Job $job): Collection
    {
        $notified = self::alreadyTold('job_fair', $job);

        return Application::with('jobseeker')
            ->where('job_id', $job->job_qualifications_id)
            ->where('match_percentage', '>=', self::APPLICANT_THRESHOLD)
            ->get()
            ->map(fn($application) => $application->jobseeker)
            ->filter()
            ->reject(fn($registration) => $notified->has($registration->jobseeker_registrations_id))
            ->map(fn($registration) => JobFairAudience::jobseekerRow($registration))
            ->values();
    }

    /** The qualified who have not applied, minus anyone already invited. */
    private static function prospectRecipients(Job $job): Collection
    {
        $notified = self::alreadyTold('job_fair_prospect', $job);

        return JobFairAudience::qualifiedForJob($job)
            ->reject(fn($registration) => $notified->has($registration->jobseeker_registrations_id))
            ->map(fn($registration) => JobFairAudience::jobseekerRow($registration))
            ->values();
    }

    private static function alreadyTold(string $type, Job $job)
    {
        return Announcement::where('type', $type)
            ->where('reference_type', 'job')
            ->where('reference_id', $job->job_qualifications_id)
            ->whereNotNull('jobseeker_id')
            ->pluck('jobseeker_id')
            ->flip();
    }

    private static function applicantMessage(Job $job, JobFairEvent $event): string
    {
        return 'You qualify for "' . $job->title . '" by ' . self::company($job)
             . ', which is being brought to ' . $event->title
             . ' on ' . self::when($event) . '. Bring your resume and a valid ID.';
    }

    private static function prospectMessage(Job $job, JobFairEvent $event): string
    {
        return 'You qualify for "' . $job->title . '" by ' . self::company($job)
             . ', which is being brought to ' . $event->title
             . ' on ' . self::when($event) . '. Apply now, then bring your'
             . ' resume and a valid ID on the day.';
    }

    /**
     * The text itself. One wording for every send, so the length — and so the
     * cost — is the same every time.
     */
    public static function applicantSmsText(Job $job, JobFairEvent $event): string
    {
        $when = $event->event_date?->format('M d, Y') ?? '';
        $time = $event->event_time
            ? \Carbon\Carbon::parse($event->event_time)->format('g:i A')
            : '';

        $text = 'PESO CDO: You qualify for ' . $job->title
              . ' (' . self::company($job) . ')'
              . ' at ' . $event->title
              . ($when ? ' on ' . $when : '')
              . ($time ? ' ' . $time : '')
              . (($where = self::venue($event)) ? ', ' . $where : '')
              . '. Bring your resume and valid ID. Do not reply.';

        // Three message parts at most.
        return Str::limit($text, 459, '');
    }

    /**
     * The text for someone who has not applied yet.
     *
     * It has to do one thing the applicant message does not: say what to do
     * next. The applicant is already on the list; this person is not, and a
     * text that only says "you qualify" leaves them with nowhere to go.
     */
    public static function prospectSmsText(Job $job, JobFairEvent $event): string
    {
        $when  = $event->event_date?->format('M d, Y') ?? '';
        $where = self::venue($event);

        $text = 'PESO CDO: You qualify for ' . $job->title
              . ' (' . self::company($job) . ')'
              . ' at ' . $event->title
              . ($when ? ' on ' . $when : '')
              . ($where ? ', ' . $where : '')
              . '. Log in to the PESO website and apply, then bring your resume'
              . ' and valid ID on the day. Do not reply.';

        return Str::limit($text, 459, '');
    }

    /**
     * Where the fair is, the way a text message should say it.
     *
     * PESO CDO client, 2026-09-13: the SMS named the venue ("Limketkai Center
     * Atrium") but not where it is, and a jobseeker who does not already know
     * the place has nothing to find it with. The address is added after the
     * name. Either may be blank on an event, so each is only added when it is
     * there, and an address that merely repeats the name is not said twice.
     */
    public static function venue(JobFairEvent $event): string
    {
        $name    = trim((string) $event->venue);
        $address = trim((string) $event->venue_address);

        if ($address !== '' && stripos($name, $address) !== false) {
            $address = '';
        }

        return implode(', ', array_filter([$name, $address], fn($part) => $part !== ''));
    }

    private static function company(Job $job): string
    {
        return $job->company->company_name ?? 'an employer';
    }

    private static function when(JobFairEvent $event): string
    {
        return $event->event_date?->format('M d, Y') ?? 'the event date';
    }
}
