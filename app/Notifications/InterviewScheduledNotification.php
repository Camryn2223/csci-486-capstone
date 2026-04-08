<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends an email when an interview is initially scheduled. Used for both
 * the applicant and the interviewers.
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
        protected string $body,
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
        $scheduledAt = $this->interview->scheduled_at->format('F j, Y \a\t g:i A');

        $interviewersList = $this->interview->interviewers->pluck('name')->implode(', ');

        return (new MailMessage)
            ->subject($this->subject)
            ->greeting("Hello {$this->recipientName},")
            ->line($this->body)
            ->line("---")
            ->line("**Interview Details:**")
            ->line("Date & Time: {$scheduledAt}")
            ->line("Position: {$this->interview->application->jobPosition->title}")
            ->line("Organization: {$this->interview->application->jobPosition->organization->name}")
            ->line("Interviewer(s): {$interviewersList}")
            ->line("---")
            ->line('If you have any questions or conflicts, please reply to this email.');
    }
}