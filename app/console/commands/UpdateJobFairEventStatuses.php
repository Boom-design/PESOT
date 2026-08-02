<?php

namespace App\Console\Commands;

use App\Models\JobFairEvent;
use Illuminate\Console\Command;

class UpdateJobFairEventStatuses extends Command
{
    protected $signature = 'jobfair:update-event-statuses';
    protected $description = 'Auto-update job fair event status based on event_date (upcoming → ongoing → completed)';

    public function handle()
    {
        $today = today();

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
}