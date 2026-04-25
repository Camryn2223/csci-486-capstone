<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationShared extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public ?string $customMessage,
        public string $pdfContent
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Shared: ' . $this->application->applicant_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.applications.shared',
        );
    }

    public function attachments(): array
    {
        $filename = preg_replace('/[^A-Za-z0-9\- ]/', '', $this->application->applicant_name) . ' - Application.pdf';

        $attachments = [
            Attachment::fromData(fn () => $this->pdfContent, $filename)
                ->withMime('application/pdf'),
        ];

        // Attach any supporting documents uploaded as part of the application
        foreach ($this->application->documents as $document) {
            $attachments[] = Attachment::fromStorageDisk('local', $document->filepath)
                ->as($document->filename)
                ->withMime($document->mimetype);
        }

        return $attachments;
    }
}