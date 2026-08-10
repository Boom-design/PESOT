<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\Application;
use App\Models\Announcement;
use Illuminate\Console\Command;

class SendInhouseParticipationReminders extends Command
{
    protected $signature = 'inhouse:send-participation-reminders';
    protected $description = 'Notify jobseekers to confirm in-house interview participation once the schedule is 5 days away';

    public function handle()
    {
        $targetDate = today()->addDays(5);

        $jobs = Job::where('schedule_type', 'inhouse')
            ->where('posting_status', 'approved')
            ->whereDate('preferred_date', '<=', $targetDate)
            ->whereDate('preferred_date', '>=', today())
            ->get();

        $totalSent = 0;

        foreach ($jobs as $job) {
            $applications = Application::where('job_id', $job->job_qualifications_id)
                ->where('inhouse_participation', 'pending')
                ->whereNull('inhouse_participation_notified_at')
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