<?php

namespace Pterodactyl\Notifications;

use Pterodactyl\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeSetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $user, public string $token)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject('Welcome - Set Your Password')
            ->greeting('Hello ' . $this->user->name . '!')
            ->line('An account has been created for you on ' . config('app.name') . '.')
            ->action('Set Your Password', url('/auth/password/reset/' . $this->token . '?email=' . urlencode($this->user->email)))
            ->line('If you did not create this account, no further action is required.');
    }
}
