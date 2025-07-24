<?php

namespace Ingenius\Core\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Core';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapTenantRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(function () {
            require __DIR__ . '/../../routes/web.php';
        });
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->name('api.')
            ->group(function () {
                require __DIR__ . '/../../routes/api.php';
            });
    }

    protected function mapTenantRoutes(): void
    {
        Route::middleware([
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
        ])->group(function () {
            require __DIR__ . '/../../routes/tenant.php';
        });
    }
}
