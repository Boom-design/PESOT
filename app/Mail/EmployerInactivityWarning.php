<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Asks an employer that has stopped posting what their status is.
 *
 * The office cannot tell a company that has shut down from one that is merely
 * between hires, and the company has no reason to log in and say. So the
 * question is sent to them.
 *
 * It is sent twice. The first letter, a month in, is a question and nothing
 * more — a quiet month is normal, and threatening an account over it would be
 * wrong. The second, a month later, carries the deadline: a week to answer,
 * after which PESO staff decide whether the account stays open. `$isFinal`
 * chooses between the two, and it is the only difference between them.
 */
class EmployerInactivityWarning extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $companyName,
        public string $contactName,
        public ?string $lastPostedOn,
        public string $disableOn,
        public int $graceDays,
        public bool $isFinal = false,
        public int $monthsQuiet = 1,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isFinal
                ? 'PESO Job Smart — Second notice: is your company still hiring?'
                : 'PESO Job Smart — Is your company still hiring?',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.employer_inactivity',
        );
    }
}
