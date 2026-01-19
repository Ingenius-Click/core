<?php

namespace Ingenius\Core\Http\Requests;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Custom email verification request for central users.
 *
 * This allows verification links to work when clicked from email clients
 * without requiring the user to be logged in first.
 */
class CentralEmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $userClass = config('core.central_user_model');
        $user = $userClass::find($this->route('id'));

        if (!$user) {
            return false;
        }

        // Verify the hash matches the user's email
        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $this->route('hash'))) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfill()
    {
        $userClass = config('core.central_user_model');
        $user = $userClass::find($this->route('id'));

        if ($user && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }
    }

    /**
     * Get the verified user instance (for convenience).
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getVerifiedUser()
    {
        $userClass = config('core.central_user_model');
        return $userClass::find($this->route('id'));
    }
}
