<?php

namespace Ingenius\Core\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Password reset notification for central app users.
 * Generates URLs pointing to central password reset routes.
 */
class CentralResetPassword extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);
        $appName = config('app.name');
        $expireMinutes = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject(__('auth::passwords.subject') . ' - ' . $appName)
            ->greeting(__('auth::passwords.greeting', ['name' => $notifiable->name]))
            ->line(__('auth::passwords.line_1'))
            ->action(__('auth::passwords.action'), $resetUrl)
            ->line(__('auth::passwords.line_2', ['minutes' => $expireMinutes]))
            ->line(__('auth::passwords.line_3'))
            ->salutation(__('auth::passwords.salutation') . ', ' . $appName);
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        // Get the frontend URL for password reset from config
        $frontendUrl = config('core.central_password_reset_url', config('app.frontend_url', ''));

        // If a frontend URL is configured, use it with query parameters
        if (!empty($frontendUrl)) {
            return $frontendUrl . '?' . http_build_query([
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        }

        // Fallback to a default URL structure
        return url('/password/reset') . '?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
