<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells an employer which of their documents came back, and why.
 *
 * PESO, 2026-10-22. The decline already wrote the desk's reasons onto the
 * folder, and an employer who signed in could read them. One who did not sign
 * in heard nothing at all, and a folder sent back is precisely the moment
 * they need to act.
 *
 * $documents is a list of [label, note] — the paper the desk sent back and
 * what they wrote about it. The note may be empty; the overall remark is
 * always there, so the employer is never told only that something is wrong.
 */
class EmployerRequirementsRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $companyName,
        public string $contactName,
        public string $staffName,
        public string $remarks,
        public array $documents,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PESO Job Smart — Some of your documents need to be resubmitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.employer_requirements_rejected',
        );
    }
}
