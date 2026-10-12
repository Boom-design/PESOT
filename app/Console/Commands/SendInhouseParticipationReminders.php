<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\Application;
use App\Models\Announcement;
use Illuminate\Console\Command;

class SendInhouseParticipationReminders extends Command
{
    protected $signature = 'inhouse:send-participation-reminders';
    protected $description = 'Remind jobseekers who never answered the in-house participation question, five days before the schedule';

    /**
     * The nudge, not the question.
     *
     * The question is asked the moment they apply. This is for the jobseeker
     * who closed that window without answering: five days before the date,
     * once, they are asked again. Exactly five — a range would send the same
     * reminder every day from T-5 to the morning of the interview.
     */
    public function handle()
    {
        $jobs = Job::where('schedule_type', 'inhouse')
            ->where('posting_status', 'approved')
            ->whereDate('preferred_date', '=', today()->addDays(5))
            ->get();

        $totalSent = 0;

        foreach ($jobs as $job) {
            $applications = Application::where('job_id', $job->job_qualifications_id)
                ->where('inhouse_participation', 'pending')
                ->get();

            foreach ($applications as $app) {
                Announcement::sendToJobseekers([
                    'type'           => 'inhouse_participation_reminder',
                    'title'          => 'Confirm Your In-house Interview 📅',
                    'message'        => 'Your in-house interview for "' . $job->title . '" is coming up on ' . \Carbon\Carbon::parse($job->preferred_date)->format('M d, Y') . '. Please confirm your participation.',
                    'reference_type' => 'job',
                    'reference_id'   => $job->job_qualifications_id,
                ], $app->jobseeker_id);

                $app->update(['inhouse_participation_notified_at' => now()]);
                $totalSent++;
            }
        }

        $this->info("Sent {$totalSent} in-house participation reminder(s).");
        return 0;
    }
}