<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public ?string $reason
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on Your Application — ' . $this->application->jobPosition->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.applications.rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}