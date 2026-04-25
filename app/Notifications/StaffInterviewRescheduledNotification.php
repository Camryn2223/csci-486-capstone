<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends an email to assigned interviewers when an interview's time is changed.
 * Contains actionable links back into the system to view the details.
 */
class StaffInterviewRescheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Interview $interview,
        protected \Carbon\Carbon $oldScheduledAt,
        protected string $recipientName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $oldTime = $this->oldScheduledAt->format('F j, Y \a\t g:i A');
        $newTime = $this->interview->scheduled_at->format('F j, Y \a\t g:i A');
        $url = route('interviews.show', $this->interview);

        return (new MailMessage)
            ->subject("Interview Rescheduled: {$this->interview->application->jobPosition->title}")
            ->greeting("Hello {$this->recipientName},")
            ->line("An interview you are assigned to for **{$this->interview->application->applicant_name}** has been rescheduled.")
            ->line("---")
            ->line("~~Previous Time: {$oldTime}~~")
            ->line("**New Time: {$newTime}**")
            ->line("---")
            ->action('View Updated Details', $url);
    }
}