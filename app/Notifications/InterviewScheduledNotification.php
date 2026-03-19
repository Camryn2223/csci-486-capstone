<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends an email to an applicant when an interview is scheduled.
 */
class InterviewScheduledNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Interview $interview,
        protected string $subject,
        protected string $body
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
        $scheduledAt = $this->interview->scheduled_at->format('F j, Y \a\t g:i A');

        return (new MailMessage)
            ->subject($this->subject)
            ->greeting("Hello {$this->interview->application->applicant_name},")
            ->line($this->body)
            ->line("---")
            ->line("**Interview Details:**")
            ->line("Date & Time: {$scheduledAt}")
            ->line("Position: {$this->interview->application->jobPosition->title}")
            ->line("Organization: {$this->interview->application->jobPosition->organization->name}")
            ->line("Interviewer: {$this->interview->interviewer->name}")
            ->line("---")
            ->line('If you need to reschedule, please reply to this email.');
    }
}