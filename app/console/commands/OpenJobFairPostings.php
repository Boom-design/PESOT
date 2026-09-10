<?php

namespace App\Console\Commands;

use App\Support\JobFairPostingWindow;
use Illuminate\Console\Command;

class OpenJobFairPostings extends Command
{
    protected $signature = 'jobfair:open-postings';
    protected $description = 'At the cutoff, take every waiting vacancy onto its job fair and show them all to jobseekers';

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

        // Sobra sa usa ka fair ang mahimong sulod sa window kung duol ang
        // ilang petsa. Ang matag usa naay kaugalingong listahan sa bakante,
        // mao nga ang matag usa naay kaugalingong cutoff.
        foreach ($events as $event) {
            $result = JobFairPostingWindow::runCutoff($event);

            $this->info($result['accepted'] === 0 && $result['opened'] === 0
                ? "Nothing left to do for \"{$event->title}\"."
                : "\"{$event->title}\" on " . $event->event_date->format('M d, Y') . ': '
                  . "{$result['accepted']} vacancy(s) taken onto the fair, "
                  . "{$result['opened']} now visible to jobseekers.");
        }

        return 0;
    }
}
