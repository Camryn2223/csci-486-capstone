<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends an email to assigned interviewers when an interview is canceled.
 * Links them back to the application context instead of the interview.
 */
class StaffInterviewCanceledNotification extends Notification
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
        $url = route('applications.show', $this->interview->application); 

        return (new MailMessage)
            ->subject("Interview Canceled: {$this->interview->application->jobPosition->title}")
            ->greeting("Hello {$this->recipientName},")
            ->line("The interview with **{$this->interview->application->applicant_name}** scheduled for **{$time}** has been canceled.")
            ->action('View Application', $url)
            ->line('No further action is required for this time slot.');
    }
}