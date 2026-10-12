<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\Job;
use Illuminate\Console\Command;

class ExpireJobPostings extends Command
{
    protected $signature = 'jobs:expire-monthly';
    protected $description = 'Close job postings whose deadline has passed and tell the employer they can repost';

    // ── PESO interview: "Kung maabot na ang expiration date, automatic nga
    // ── ma-expire ang vacancy ug kinahanglan na kini i-update o i-post
    // ── pag-usab." Ang scopeActive nagtago na sa nalabyan nga posting, apan
    // ── ang row nagpabilin nga status='open' — mao nga walay makahibalo ang
    // ── employer nga wala na diay siya makadawat ug applicant. Dinhi kini
    // ── ma-close nga tinuod ug ma-notify ang employer.
    // ──
    // ── Ang row wala gi-delete. Ang Reports nagsalig niini — apil ang
    // ── qualification requirements ug ang listahan sa na-hire. ──
    public function handle()
    {
        $expired = Job::with('company')
            ->where('status', 'open')
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', today())
            ->get();

        $notified = 0;

        foreach ($expired as $job) {
            $job->update(['status' => 'closed']);

            // Usa ra ka notice kada posting group. Ang usa ka position nga
            // gi-post sa tulo ka channel kay tulo ka row — apan usa ra gihapon
            // ka bakante, mao nga usa ra pud ka mensahe ang angay.
            $isGroupLeader = $job->posting_group_id === null
                || $job->posting_group_id === $job->job_qualifications_id;

            if (!$isGroupLeader || !$job->company) {
                continue;
            }

            Announcement::sendToEmployers([
                'type'           => 'job_posting_expired',
                'title'          => 'Job Posting Expired ⏳',
                'message'        => 'Your posting for "' . $job->title . '" reached its deadline of '
                                    . $job->deadline->format('M d, Y') . ' and is now closed. '
                                    . 'Post it again if you are still hiring.',
                'reference_type' => 'job',
                'reference_id'   => $job->job_qualifications_id,
            ], $job->company_id);

            $notified++;
        }

        $this->info("Closed {$expired->count()} expired job posting(s); notified {$notified} employer(s).");
        return 0;
    }
}
