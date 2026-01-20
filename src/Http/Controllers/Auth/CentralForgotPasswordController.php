<?php

namespace Ingenius\Core\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Ingenius\Core\Http\Controllers\Controller;

class CentralForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return Response::api(
                data: null,
                message: __('auth::passwords.sent'),
            );
        }

        // For security reasons, we return a generic success message even if the email
        // doesn't exist in the database. This prevents email enumeration attacks.
        return Response::api(
            data: null,
            message: __('auth::passwords.sent'),
        );
    }
}
