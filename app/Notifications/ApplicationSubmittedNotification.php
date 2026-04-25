<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends an email when a new application is submitted for a position to users
 * who have enabled application notifications and have the right permission.
 */
class ApplicationSubmittedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Application $application,
        protected string $recipientName
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->application->jobPosition->title;
        $orgName = $this->application->jobPosition->organization->name;
        $appUrl = route('applications.show', $this->application);

        return (new MailMessage)
            ->subject("New Application Submitted: {$jobTitle}")
            ->greeting("Hello {$this->recipientName},")
            ->line("A new application has been submitted by **{$this->application->applicant_name}** for the **{$jobTitle}** position at **{$orgName}**.")
            ->action('Review Application', $appUrl)
            ->line('You are receiving this email because you have notifications enabled for new applications you can review.');
    }
}