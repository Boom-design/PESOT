<?php

namespace App\Console\Commands;

use App\Models\JobFairEvent;
use App\Models\JobFairRegistration;
use App\Models\Announcement;
use Illuminate\Console\Command;

class SendJobFairAttendanceConfirmations extends Command
{
    protected $signature = 'jobfair:send-attendance-confirmations';
    protected $description = 'Send attendance confirmation prompts to jobseekers registered to job fair events happening today';

    public function handle()
    {
        $todayEvents = JobFairEvent::whereDate('event_date', today())->get();

        $totalSent = 0;

        foreach ($todayEvents as $event) {
            $registrations = JobFairRegistration::with('jobseeker')
                ->where('job_fair_id', $event->job_fair_events_id)
                ->whereNull('is_attended')
                ->whereNull('attendance_notified_at')
                ->get();

            foreach ($registrations as $reg) {
                if (!$reg->user_id) continue;

                Announcement::sendToJobseekers([
                    'type'           => 'job_fair_attendance_confirm',
                    'title'          => 'Job Fair Attendance Confirmation 📋',
                    'message'        => 'Did you attend/participate in ' . $event->title . ' today at ' . $event->venue . '? Please confirm your attendance.',
                    'reference_type' => 'job_fair_registration',
                    'reference_id'   => $reg->job_fair_registrations_id,
                ], $reg->user_id);

                $reg->update(['attendance_notified_at' => now()]);
                $totalSent++;
            }
        }

        $this->info("Sent {$totalSent} attendance confirmation(s).");
        return 0;
    }
}