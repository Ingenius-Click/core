<?php

use Illuminate\Support\Facades\Route;
use Ingenius\Core\Http\Controllers\AuthController;
use Ingenius\Core\Http\Controllers\TenantAssetsController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::prefix('central')->group(function () {});

// Custom tenant assets route with our middleware
Route::get('/tenancy/assets/{path?}', [TenantAssetsController::class, 'asset'])
    ->where('path', '(.*)')
    ->name('stancl.tenancy.asset');
