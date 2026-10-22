<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells an employer they have been invited to a job fair.
 *
 * PESO, 2026-10-23: the invitation was a bell notice and nothing else, and a
 * bell only reaches an employer who happens to sign in. They have a deadline
 * to answer by — the office moves on to other employers after it — so the one
 * invitation that matters most was the one easiest to miss.
 *
 * The email does not accept or decline anything. It says a fair is on, when
 * and where, and by when they must answer; the answer itself is given on
 * their own page, where the office can see it.
 */
class JobFairInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $companyName,
        public string $contactName,
        public string $eventTitle,
        public string $eventDate,
        public string $eventTime,
        public string $venue,
        public string $deadline,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PESO Job Smart — You are invited to ' . $this->eventTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.job_fair_invitation',
        );
    }
}
