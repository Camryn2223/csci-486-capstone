<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends an email to staff members when they are scheduled to conduct an interview.
 * Contains actionable links back into the system to view the details.
 */
class StaffInterviewScheduledNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Interview $interview,
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
        $url = route('interviews.show', $this->interview);

        return (new MailMessage)
            ->subject("Interview Scheduled: {$this->interview->application->jobPosition->title}")
            ->greeting("Hello {$this->recipientName},")
            ->line("You have been scheduled to conduct an interview for the **{$this->interview->application->jobPosition->title}** position.")
            ->line("---")
            ->line("**Interview Details:**")
            ->line("Applicant: {$this->interview->application->applicant_name}")
            ->line("Date & Time: {$scheduledAt}")
            ->line("Assigned Interviewer(s): {$interviewersList}")
            ->line("---")
            ->action('View Interview Details', $url)
            ->line('Please review the application and prepare any necessary notes before the scheduled time.');
    }
}