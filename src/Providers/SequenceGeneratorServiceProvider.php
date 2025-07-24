<?php

namespace Ingenius\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Ingenius\Core\Services\SequenceGeneratorService;

class SequenceGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SequenceGeneratorService::class, function ($app) {
            return new SequenceGeneratorService();
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/sequences.php',
            'sequences'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/sequences.php' => config_path('sequences.php'),
        ], 'config');
    }
}
