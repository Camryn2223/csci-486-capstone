<?php

namespace App\Notifications;

use App\Models\OrganizationInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends an email invite to an external recipient with a signed registration
 * link that pre-authorizes them to create an account without entering the
 * code manually.
 */
class OrganizationInviteNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly OrganizationInvite $invite
    ) {}

    /**
     * @return string[]
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $registrationUrl = route('register', ['invite' => $this->invite->code]);

        return (new MailMessage)
            ->subject("You've been invited to join {$this->invite->organization->name}")
            ->greeting("Hello!")
            ->line("You have been invited to join **{$this->invite->organization->name}** as an interviewer.")
            ->action('Accept Invitation & Create Account', $registrationUrl)
            ->line('This invite link can only be used once. If you did not expect this invitation, you can ignore this email.')
            ->line('Alternatively, you can register manually at ' . route('register') . ' and enter the code: **' . $this->invite->code . '**');
    }
}