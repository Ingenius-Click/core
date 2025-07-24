<?php

namespace Ingenius\Core\Traits;

use Ingenius\Core\Support\MigrationRegistry;

trait RegistersMigrations
{
    /**
     * Register migrations with the registry.
     *
     * @param string $path
     * @param string $package
     * @return void
     */
    protected function registerMigrations(string $path, string $package): void
    {
        $registry = app(MigrationRegistry::class);
        $registry->register($path, $package);

        // Register package path
        $this->registerPackagePath($package);
    }

    /**
     * Register tenant migrations with the registry.
     *
     * @param string $path
     * @param string $package
     * @return void
     */
    protected function registerTenantMigrations(string $path, string $package): void
    {
        $registry = app(MigrationRegistry::class);
        $registry->registerTenant($path, $package);

        // Register package path
        $this->registerPackagePath($package);
    }

    /**
     * Register package path with the registry.
     *
     * @param string $package
     * @return void
     */
    protected function registerPackagePath(string $package): void
    {
        $reflection = new \ReflectionClass(get_class($this));
        $packagePath = dirname(dirname(dirname($reflection->getFileName())));

        $registry = app(MigrationRegistry::class);
        $registry->registerPackagePath($package, $packagePath);
    }
}
