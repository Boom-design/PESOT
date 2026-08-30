<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells an employer their account has been switched back on.
 *
 * An inactive employer has no reason to keep checking the site — that is why
 * the account went inactive in the first place. The bell notice only reaches
 * someone who signs in, so the good news is emailed the same way the warning
 * was.
 */
class EmployerAccountReactivated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $companyName,
        public string $contactName,
        public string $staffName,
        public int $reopenedPostings,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PESO Job Smart — Your account is active again',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.employer_reactivated',
        );
    }
}
