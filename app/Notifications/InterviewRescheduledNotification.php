<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends an email to the applicant and assigned interviewers when an interview's
 * time is changed.
 */
class InterviewRescheduledNotification extends Notification
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
        
        $interviewersList = $this->interview->interviewers->pluck('name')->implode(', ');

        return (new MailMessage)
            ->subject("Interview Rescheduled: {$this->interview->application->jobPosition->title}")
            ->greeting("Hello {$this->recipientName},")
            ->line("The interview for the **{$this->interview->application->jobPosition->title}** position has been rescheduled.")
            ->line("---")
            ->line("~~Previous Time: {$oldTime}~~")
            ->line("**New Time: {$newTime}**")
            ->line("Interviewer(s): {$interviewersList}")
            ->line("---")
            ->line('Please let us know if this new time does not work for you.');
    }
}