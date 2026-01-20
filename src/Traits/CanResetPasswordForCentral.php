<?php

namespace Ingenius\Core\Traits;

use Ingenius\Core\Notifications\CentralResetPassword;

/**
 * Trait to add password reset support to central user models.
 *
 * Usage:
 * 1. Add this trait to your central User model
 * 2. Implement Illuminate\Contracts\Auth\CanResetPassword interface
 *
 * Example:
 * class User extends Authenticatable implements CanResetPassword
 * {
 *     use CanResetPasswordForCentral;
 * }
 */
trait CanResetPasswordForCentral
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // Prevent duplicate emails in the same request
        $cacheKey = "password_reset_sent_central_{$this->getKey()}";

        if (cache()->has($cacheKey)) {
            return;
        }

        $this->notify(new CentralResetPassword($token));

        // Cache for 5 seconds to prevent duplicates in same request cycle
        cache()->put($cacheKey, true, 5);
    }
}
