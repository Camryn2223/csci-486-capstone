<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends an email to the applicant and assigned interviewers when an interview
 * is canceled.
 */
class InterviewCanceledNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Interview $interview,
        protected string $recipientName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $time = $this->interview->scheduled_at->format('F j, Y \a\t g:i A');

        return (new MailMessage)
            ->subject("Interview Canceled: {$this->interview->application->jobPosition->title}")
            ->greeting("Hello {$this->recipientName},")
            ->line("The interview scheduled for **{$time}** for the **{$this->interview->application->jobPosition->title}** position has been canceled.")
            ->line('If you have any questions, please feel free to reach out.');
    }
}