<?php

namespace App\Console\Commands;

use App\Support\JobFairPostingWindow;
use Illuminate\Console\Command;

class OpenJobFairPostings extends Command
{
    protected $signature = 'jobfair:open-postings';
    protected $description = 'Open every approved job fair posting once a job fair event is close enough for jobseekers to plan around';

    // ── PESO 2026-08-13. Ang event i-create dili moubos sa 10 ka adlaw nga
    // ── abante; ang mga posting mo-abli sa tunga niini — 5 ka adlaw sa dili pa
    // ── ang fair. Kaniadto mo-abli sila dayon pag-create sa event, mao nga
    // ── napulo ka adlaw nga makakita ang jobseeker ug bakante nga dili pa
    // ── niya maadtoan.
    // ──
    // ── Adlaw-adlaw ni modagan. Ang pag-abli usa ra ka higayon kada posting:
    // ── ang pendingPostings() nangita ra ug status='closed', mao nga ang na-
    // ── abli na kagahapon dili na siya makit-an ugma. ──
    public function handle()
    {
        $events = JobFairPostingWindow::eventsInWindow();

        if ($events->isEmpty()) {
            $this->info('No job fair event within ' . JobFairPostingWindow::daysBefore() . ' day(s). Nothing opened.');
            return 0;
        }

        $event  = $events->first();
        $opened = JobFairPostingWindow::openAll($event);

        $this->info($opened === 0
            ? 'No job fair posting was waiting. Nothing opened.'
            : "Opened {$opened} job fair posting(s) ahead of \"{$event->title}\" on "
              . $event->event_date->format('M d, Y') . '.');

        return 0;
    }
}
