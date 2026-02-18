<?php

use Illuminate\Support\Facades\Route;
use Ingenius\Core\Http\Controllers\TenantAssetsController;
use Ingenius\Core\Http\Middleware\ConfigureSanctumStatefulDomains;
use Ingenius\Core\Http\Middleware\InitializeTenancyByRequestDataIfPresent;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::prefix('central')->group(function () {});

/*
 * Sanctum CSRF Cookie — tenant-aware override.
 *
 * Only active when 'routes' => false in config/sanctum.php; otherwise Sanctum's
 * auto-registered route takes over and this block is unreachable.
 *
 * Middleware order is intentional:
 *   1. InitializeTenancyByRequestDataIfPresent — initializes the tenant when an
 *      X-Tenant header or ?tenant param is present; skips gracefully for central
 *      requests so the central session is used instead.
 *   2. ConfigureSanctumStatefulDomains — reads the domain from the already-initialized
 *      tenant (if any) to register it as stateful and set SameSite=None for
 *      cross-domain cookie delivery. No-ops for central requests.
 */
Route::group(['prefix' => config('sanctum.prefix', 'sanctum')], static function () {
    Route::get('/csrf-cookie', [CsrfCookieController::class, 'show'])
        ->middleware([
            InitializeTenancyByRequestDataIfPresent::class,
            ConfigureSanctumStatefulDomains::class,
        ])
        ->name('sanctum.csrf-cookie');
});

// Custom tenant assets route with our middleware
Route::get('/tenancy/assets/{path?}', [TenantAssetsController::class, 'asset'])
    ->where('path', '(.*)')
    ->name('stancl.tenancy.asset');
