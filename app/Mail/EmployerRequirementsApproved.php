<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells an employer the office has accepted their documents.
 *
 * PESO, 2026-10-22: an employer who has submitted their folder has nothing to
 * do but wait, and nothing tells them when the waiting is over. The bell
 * notice only reaches someone who signs in, and the person with no reason to
 * sign in is exactly the one waiting to hear. So the answer is emailed, the
 * same way the inactivity warning and the reactivation already are.
 *
 * $releasedPostings is the count of vacancies that were held back while the
 * folder was being read and are now visible — see
 * JobPostingNotice::releaseHeldPostings(). It is worth saying out loud,
 * because the employer wrote those postings and watched them stay hidden.
 */
class EmployerRequirementsApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $companyName,
        public string $contactName,
        public string $staffName,
        public int $releasedPostings,
        public int $jobFairInvites,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PESO Job Smart — Your requirements have been approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.employer_requirements_approved',
        );
    }
}
