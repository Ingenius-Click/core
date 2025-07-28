<?php

use Illuminate\Support\Facades\Route;
use Ingenius\Core\Http\Controllers\AuthController;
use Ingenius\Core\Http\Controllers\TemplateController;
use Ingenius\Core\Http\Controllers\TenantsController;

Route::prefix('central')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('core.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('templates', [TemplateController::class, 'index']);

        Route::get('tenants', [TenantsController::class, 'index']);
        Route::post('tenants', [TenantsController::class, 'store']);
        Route::put('tenants/{tenant}/styles', [TenantsController::class, 'updateStyles']);
    });
});
