<?php

namespace Ingenius\Core\Traits;

use Ingenius\Core\Notifications\CentralVerifyEmail;

/**
 * Trait to add email verification support to central user models.
 *
 * Usage:
 * 1. Add this trait to your central User model
 * 2. Implement Illuminate\Contracts\Auth\MustVerifyEmail interface
 *
 * Example:
 * class User extends Authenticatable implements MustVerifyEmail
 * {
 *     use MustVerifyEmailForCentral;
 * }
 */
trait MustVerifyEmailForCentral
{
    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        // Prevent duplicate emails in the same request
        $cacheKey = "verification_sent_central_{$this->getKey()}";

        if (cache()->has($cacheKey)) {
            return;
        }

        $this->notify(new CentralVerifyEmail);

        // Cache for 5 seconds to prevent duplicates in same request cycle
        cache()->put($cacheKey, true, 5);
    }
}
