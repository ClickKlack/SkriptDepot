<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Einladungsmail mit signiertem Link, über den der Nutzer sein Passwort selbst setzt.
 */
class UserInvitation extends Notification
{
    public function __construct(
        public readonly string $acceptUrl,
        public readonly int $validDays,
    ) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('skriptdepot.invitation.mail.subject'))
            ->greeting(__('skriptdepot.invitation.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('skriptdepot.invitation.mail.intro'))
            ->action(__('skriptdepot.invitation.mail.action'), $this->acceptUrl)
            ->line(__('skriptdepot.invitation.mail.expiry', ['days' => $this->validDays]))
            ->line(__('skriptdepot.invitation.mail.outro'));
    }
}
