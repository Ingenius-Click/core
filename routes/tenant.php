<?php

use Illuminate\Support\Facades\Route;
use Ingenius\Core\Http\Controllers\SettingsController;
use Ingenius\Core\Http\Controllers\StoreConfigurationController;
use Ingenius\Core\Http\Controllers\TenantsController;
use Ingenius\Core\Http\Controllers\NotificationConfigurationsController;

Route::prefix('api')->middleware(['api'])->group(function () {

    Route::prefix('contact')->group(function () {
        Route::post('/', [\Ingenius\Core\Http\Controllers\ContactController::class, 'contact']);
    });

    Route::prefix('layout')->group(function () {
        Route::get('/', [TenantsController::class, 'getLayout']);
    });

    Route::prefix('settings')->middleware('tenant.user')->group(function () {
        Route::get('/{group}', [SettingsController::class, 'getGroup']);
        Route::put('/{group}', [SettingsController::class, 'updateSettings']);
    });

    Route::prefix('store-config')->group(function () {
        Route::get('/', [StoreConfigurationController::class, 'getStoreConfiguration']);
    });

    Route::prefix('notifications')->middleware('tenant.user')->group(function () {
        Route::prefix('configurations')->group(function () {
            Route::post('/', [NotificationConfigurationsController::class, 'createOrEdit'])
                    ->middleware(['tenant.has.feature:manage-notifications'])
            ;
            Route::get('/', [NotificationConfigurationsController::class, 'getAll'])
                ->middleware(['tenant.has.feature:view-notifications'])
            ;
            Route::get('/by-event-channel', [NotificationConfigurationsController::class, 'getByEventAndChannel'])
                ->middleware(['tenant.has.feature:view-notifications'])
            ;

            Route::put('/{configuration}', [NotificationConfigurationsController::class, 'update'])
                ->middleware(['tenant.has.feature:manage-notifications'])
            ;
            Route::patch('/{configuration}/toggle-enable', [NotificationConfigurationsController::class, 'toggleEnable'])
                ->middleware(['tenant.has.feature:manage-notifications'])
            ;
        });

        Route::get('/events', [\Ingenius\Core\Http\Controllers\EventNotificationsController::class, 'registeredEvents'])
            ->middleware(['tenant.has.feature:view-notifications'])
        ;
    });
});
