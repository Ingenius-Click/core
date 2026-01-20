<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Ingenius\Core\Http\Controllers\Auth\CentralForgotPasswordController;
use Ingenius\Core\Http\Controllers\Auth\CentralResetPasswordController;
use Ingenius\Core\Http\Controllers\AuthController;
use Ingenius\Core\Http\Controllers\TemplateController;
use Ingenius\Core\Http\Controllers\TenantsController;
use Ingenius\Core\Http\Requests\CentralEmailVerificationRequest;

Route::prefix('central')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('core.login');

    // Email Verification Routes for Central Users
    Route::prefix('email')->group(function () {
        // Email verification handler
        Route::get('/verify/{id}/{hash}', function (CentralEmailVerificationRequest $request) {
            $request->fulfill();

            if ($request->wantsJson()) {
                return Response::api(
                    data: ['verified' => true],
                    message: 'Email verified successfully!'
                );
            }

            // Redirect to frontend dashboard or configured URL
            $redirectUrl = config('core.central_email_verification_redirect_url', config('app.frontend_url', '/') . '/email/verified');

            return redirect($redirectUrl);
        })->middleware(['signed'])->name('central.verification.verify');

        // Resend verification email (requires authentication)
        Route::post('/verification-notification', function (Request $request) {
            $user = $request->user();

            if (!$user) {
                return Response::api(
                    data: null,
                    message: 'Unauthorized',
                    code: 401
                );
            }

            if ($user->hasVerifiedEmail()) {
                return Response::api(
                    data: null,
                    message: 'Email already verified.',
                    code: 400
                );
            }

            $user->sendEmailVerificationNotification();

            if ($request->wantsJson()) {
                return Response::api(
                    data: null,
                    message: 'Verification link sent!'
                );
            }

            return back()->with('message', 'Verification link sent!');
        })->middleware(['auth:sanctum', 'throttle:6,1'])->name('central.verification.send');
    });

    // Password Reset Routes for Central Users
    Route::prefix('password')->group(function () {
        // Send password reset link
        Route::post('/forgot', [CentralForgotPasswordController::class, 'sendResetLinkEmail'])
            ->middleware(['throttle:6,1'])
            ->name('central.password.email');

        // Reset password
        Route::post('/reset', [CentralResetPasswordController::class, 'reset'])
            ->name('central.password.reset');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('templates')->group(function () {
            Route::get('/', [TemplateController::class, 'index']);
            Route::post('/', [TemplateController::class, 'store']);
            Route::put('{template}', [TemplateController::class, 'update']);
            Route::get('{template}/styles', [TemplateController::class, 'getStyles']);
            Route::get('{template}', [TemplateController::class, 'show']);
        });

        Route::get('tenants', [TenantsController::class, 'index']);
        Route::post('tenants', [TenantsController::class, 'store']);
        Route::put('tenants/{tenant}/styles', [TenantsController::class, 'updateStyles']);

        Route::get('features', [\Ingenius\Core\Http\Controllers\FeatureController::class, 'index']);
    });
});
