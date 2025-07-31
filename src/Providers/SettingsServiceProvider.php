<?php

namespace Ingenius\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Ingenius\Core\Services\SettingsService;
use Ingenius\Core\Settings\CustomizeSettings;

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

        // Register contextual Settings bindings
        $this->registerSettingsBindings();
    }

    /**
     * Register Settings class bindings that work in tenant context
     */
    protected function registerSettingsBindings(): void
    {
        // Bind CustomizeSettings to use make() method when in tenant context
        $this->app->bind(CustomizeSettings::class, function ($app) {
            // Check if we're in tenant context
            $tenancy = $app->make(\Stancl\Tenancy\Tenancy::class);
            if ($tenancy->tenant) {
                return CustomizeSettings::make();
            }

            // Return empty instance if not in tenant context
            return new CustomizeSettings();
        });
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
