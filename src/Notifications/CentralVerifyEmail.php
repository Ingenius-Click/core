<?php

namespace Ingenius\Core\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email verification notification for central app users.
 * Uses Laravel's default verification URL generation.
 */
class CentralVerifyEmail extends BaseVerifyEmail
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $appName = config('app.name');
        $expireMinutes = config('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject(__('auth::verification.subject') . ' - ' . $appName)
            ->greeting(__('auth::verification.greeting', ['name' => $notifiable->name]))
            ->line(__('auth::verification.line_1'))
            ->action(__('auth::verification.action'), $verificationUrl)
            ->line(__('auth::verification.line_2', ['minutes' => $expireMinutes]))
            ->line(__('auth::verification.line_3'))
            ->salutation(__('auth::verification.salutation') . ', ' . $appName);
    }
}
