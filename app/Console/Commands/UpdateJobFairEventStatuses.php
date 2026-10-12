<?php

namespace App\Console\Commands;

use App\Models\JobFairEvent;
use Illuminate\Console\Command;

class UpdateJobFairEventStatuses extends Command
{
    protected $signature = 'jobfair:update-event-statuses';
    protected $description = 'Auto-update job fair event status based on event_date, and stamp the DOLE submission day';

    public function handle()
    {
        $today = today();

        $this->stampDoleCutoffs($today);

        $toOngoing = JobFairEvent::whereDate('event_date', $today)
            ->where('status', '!=', 'ongoing')
            ->where('status', '!=', 'completed')
            ->update(['status' => 'ongoing']);

        $toCompleted = JobFairEvent::whereDate('event_date', '<', $today)
            ->where('status', '!=', 'completed')
            ->update(['status' => 'completed']);

        $this->info("Updated {$toOngoing} event(s) to Ongoing, {$toCompleted} event(s) to Completed.");
        return 0;
    }

    /**
     * Record the day the office submits its confirmed roster to DOLE.
     *
     * Project manager, 2026-08-23: ten days before the fair the employer slots
     * are meant to be filled, and that roster is what goes to DOLE. Stamping
     * the day rather than computing it on the fly matters because the report
     * has to say which confirmations were in hand at the time, and an event
     * whose date is later edited must not silently rewrite what was submitted.
     *
     * Nothing is blocked here. A confirmation after this day is still taken;
     * it is only marked as arriving after the submission.
     */
    private function stampDoleCutoffs($today): void
    {
        $daysBefore = (int) config('peso.jobfair.dole_cutoff_days_before');

        $events = JobFairEvent::whereNull('dole_cutoff_at')
            ->whereDate('event_date', '>=', $today)
            ->where('status', '!=', 'completed')
            ->get();

        $stamped = 0;

        foreach ($events as $event) {
            if ($event->daysUntil() > $daysBefore) {
                continue;
            }

            // An event created inside the window never had a submission day to
            // reach. Stamping one on its first night would claim a roster was
            // sent when the office had not even finished inviting.
            if ($event->created_at && $event->created_at->diffInDays($event->event_date, false) <= $daysBefore) {
                continue;
            }

            // Ang petsa nga gi-stamp mao ang lagda mismo — napulo ka adlaw sa
            // dili pa ang fair — dili ang adlaw nga nakadagan kining command.
            // Kung malaktawan ang scheduler ug usa ka gabii, ang tinuod nga
            // deadline wala nabalhin, ug ang laray nga mi-confirm human niini
            // dili angay maihap nga naabtan pa.
            $event->update(['dole_cutoff_at' => $event->doleCutoffDate()]);
            $this->info('"' . $event->title . '" — DOLE roster day reached.');
            $stamped++;
        }

        if ($stamped) {
            $this->info("Stamped {$stamped} event(s) with a DOLE submission day.");
        }
    }
}