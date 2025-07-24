<?php

namespace Ingenius\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Ingenius\Core\Services\SettingsService;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/settings.php',
            'settings'
        );

        $this->app->singleton(SettingsService::class, function ($app) {
            return new SettingsService();
        });

        $this->app->alias(SettingsService::class, 'settings');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/settings.php' => config_path('settings.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }
}
