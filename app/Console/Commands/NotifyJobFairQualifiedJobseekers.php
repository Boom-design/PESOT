<?php

namespace App\Console\Commands;

use App\Support\JobFairNotifier;
use Illuminate\Console\Command;

/**
 * Tell the qualified about every open job fair vacancy, nightly.
 *
 * PESO Job Fair staff, 2026-10-23: this used to be two buttons on every
 * vacancy, pressed by hand. A fair carries dozens of vacancies and the press
 * was never a decision — the rules decide who is texted. A missed press was
 * invisible, so the people who would have heard simply did not, and nobody
 * found out until the fair was over.
 *
 * Every guard the buttons had lives in JobFairNotifier and still applies: the
 * fair must have met its confirmed-employer gate, the vacancy must be open,
 * and nobody is texted twice about the same vacancy.
 *
 * Runs once a day, not every minute. A new person becoming qualified is not
 * urgent to the minute, and a nightly run keeps the texts in one batch where
 * the cost can be read off one log line.
 */
class NotifyJobFairQualifiedJobseekers extends Command
{
    protected $signature = 'jobfair:notify-qualified';

    protected $description = 'Text the jobseekers who qualify for each open job fair vacancy';

    public function handle(): int
    {
        $vacancies = JobFairNotifier::dueVacancies();

        if ($vacancies->isEmpty()) {
            $this->info('No open job fair vacancy on a fair that has met its gate.');
            return self::SUCCESS;
        }

        $people = $sent = $failed = 0;

        foreach ($vacancies as $job) {
            $event = JobFairNotifier::eventOf($job);
            if (!$event) {
                continue;
            }

            $result = JobFairNotifier::notifyVacancy($job, $event);

            $reached = $result['applicants'] + $result['prospects'];
            if ($reached === 0) {
                continue;
            }

            $people += $reached;
            $sent   += $result['sent'];
            $failed += $result['failed'];

            $this->line(sprintf(
                '  %-34s %-26s %d applicant(s), %d prospect(s), %d text(s) sent%s',
                \Illuminate\Support\Str::limit($job->title, 32),
                \Illuminate\Support\Str::limit($event->title, 24),
                $result['applicants'],
                $result['prospects'],
                $result['sent'],
                $result['failed'] > 0 ? ', ' . $result['failed'] . ' failed' : ''
            ));
        }

        $this->info($vacancies->count() . ' vacanc(y/ies) checked, ' . $people
            . ' jobseeker(s) notified, ' . $sent . ' text message(s) sent'
            . ($failed > 0 ? ', ' . $failed . ' failed' : '')
            . (\App\Support\PhilSms::enabled() ? '' : ' (test mode — nothing was actually sent)') . '.');

        return self::SUCCESS;
    }
}
