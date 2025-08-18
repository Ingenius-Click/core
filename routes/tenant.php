<?php

use Illuminate\Support\Facades\Route;
use Ingenius\Core\Http\Controllers\SettingsController;
use Ingenius\Core\Http\Controllers\StoreConfigurationController;
use Ingenius\Core\Http\Controllers\TenantsController;

Route::prefix('api')->middleware(['api'])->group(function () {

    Route::prefix('layout')->group(function () {
        Route::get('/', [TenantsController::class, 'getLayout']);
    });

    Route::prefix('settings')->middleware('tenant.user')->group(function () {
        Route::get('/{group}', [SettingsController::class, 'getGroup']);
        Route::put('/{group}', [SettingsController::class, 'updateSetting']);
    });

    Route::prefix('store-config')->group(function () {
        Route::get('/', [StoreConfigurationController::class, 'getStoreConfiguration']);
    });
});
