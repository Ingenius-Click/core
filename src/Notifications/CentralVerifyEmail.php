<?php

namespace Ingenius\Core\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

/**
 * Email verification notification for central app users.
 * Generates URLs pointing to central verification routes.
 */
class CentralVerifyEmail extends BaseVerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'api.central.verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

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
