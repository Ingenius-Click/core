<?php

use Illuminate\Support\Facades\Route;
use Ingenius\Core\Http\Controllers\SettingsController;

Route::prefix('api')->middleware(['api'])->group(function () {
    Route::prefix('settings')->middleware('tenant.user')->group(function () {
        Route::get('/{group}', [SettingsController::class, 'getGroup']);
        Route::put('/{group}', [SettingsController::class, 'updateSetting']);
    });
});
