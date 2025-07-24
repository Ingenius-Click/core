<?php

namespace Ingenius\Core\Providers;

use Ingenius\Core\Support\TenantAwareUrlGenerator;
use Illuminate\Support\ServiceProvider;

class MediaLibraryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override the media-library url_generator config
        $this->app['config']->set('media-library.url_generator', TenantAwareUrlGenerator::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // No additional boot actions needed
    }
}
