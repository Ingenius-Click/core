<?php

namespace Ingenius\Core\Traits;

use Ingenius\Core\Support\ConfigRegistry;

trait RegistersConfigurations
{
    /**
     * Register a configuration file with the registry.
     *
     * @param string $path
     * @param string $key
     * @param string $package
     * @return void
     */
    protected function registerConfig(string $path, string $key, string $package): void
    {
        $registry = app(ConfigRegistry::class);
        $registry->register($path, $key, $package);
    }

    /**
     * Register all configuration files in a directory with the registry.
     *
     * @param string $directory
     * @param string $package
     * @param string|null $prefix
     * @return void
     */
    protected function registerConfigDirectory(string $directory, string $package, ?string $prefix = null): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob($directory . '/*.php');
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $key = $prefix ? $prefix . '.' . $filename : $filename;

            $this->registerConfig($file, $key, $package);
        }
    }

    /**
     * Publish configuration files.
     *
     * @param string $directory
     * @param string $package
     * @return array
     */
    protected function publishConfigs(string $directory, string $package): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = glob($directory . '/*.php');
        $publishes = [];

        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $publishes[$file] = config_path($package . '/' . $filename . '.php');
        }

        return $publishes;
    }
}
