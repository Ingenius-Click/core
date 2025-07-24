<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            return response()->api(
                message: 'User not found',
                status: 404,
            );
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->api(
                message: 'Invalid credentials',
                status: 401,
            );
        }

        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->api(
                message: 'Login successful',
                data: ['token' => $token],
            );
        }

        return redirect()->intended(route('core.dashboard'));
    }
}
