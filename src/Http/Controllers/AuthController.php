<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Ingenius\Core\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $userClass = config('core.central_user_model');

        $user = $userClass::where('email', $request->email)->first();

        if (!$user) {
            return Response::api(
                message: 'User not found',
                code: 404,
            );
        }

        if (!Hash::check($request->password, $user->password)) {
            return Response::api(
                message: 'Invalid credentials',
                code: 401,
            );
        }

        // Check if email verification is required
        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            // Resend verification email
            if (method_exists($user, 'sendEmailVerificationNotification')) {
                $user->sendEmailVerificationNotification();
            }

            if ($request->wantsJson()) {
                return Response::api(
                    message: 'Your email address is not verified. A new verification link has been sent to your email.',
                    data: ['email_verified' => false],
                    code: 403,
                );
            }

            return back()->withErrors([
                'email' => 'Please verify your email address before logging in. A new verification link has been sent to your email.',
            ])->withInput($request->except('password'));
        }

        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return Response::api(
                message: 'Login successful',
                data: ['token' => $token],
            );
        }

        return redirect()->intended(route('core.dashboard'));
    }
}
