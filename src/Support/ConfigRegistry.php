<?php

namespace Ingenius\Core\Support;

class ConfigRegistry
{
    /**
     * The registered configurations.
     *
     * @var array
     */
    protected $configs = [];

    /**
     * Register a configuration file.
     *
     * @param string $path
     * @param string $key
     * @param string $package
     * @return void
     */
    public function register(string $path, string $key, string $package): void
    {
        if (!in_array(['path' => $path, 'key' => $key, 'package' => $package], $this->configs)) {
            $this->configs[] = ['path' => $path, 'key' => $key, 'package' => $package];
        }
    }

    /**
     * Get all registered configurations.
     *
     * @return array
     */
    public function all(): array
    {
        // Sort configs to ensure 'core' package configs are first
        $sortedConfigs = $this->configs;

        usort($sortedConfigs, function ($a, $b) {
            if ($a['package'] === 'core') {
                return -1;
            }
            if ($b['package'] === 'core') {
                return 1;
            }
            return 0;
        });

        return $sortedConfigs;
    }

    /**
     * Get all configuration files for a specific package.
     *
     * @param string $package
     * @return array
     */
    public function forPackage(string $package): array
    {
        return array_filter($this->configs, function ($config) use ($package) {
            return $config['package'] === $package;
        });
    }

    /**
     * Get a specific configuration by key.
     *
     * @param string $key
     * @return array|null
     */
    public function forKey(string $key): ?array
    {
        $configs = array_filter($this->configs, function ($config) use ($key) {
            return $config['key'] === $key;
        });

        return !empty($configs) ? reset($configs) : null;
    }

    /**
     * Get all configuration keys.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_map(function ($config) {
            return $config['key'];
        }, $this->configs);
    }

    /**
     * Check if a configuration key exists.
     *
     * @param string $key
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        return $this->forKey($key) !== null;
    }

    /**
     * Load all registered configurations.
     *
     * @return void
     */
    public function loadAll(): void
    {
        foreach ($this->all() as $config) {
            $this->loadConfig($config);
        }
    }

    /**
     * Load a specific configuration.
     *
     * @param array $config
     * @return void
     */
    protected function loadConfig(array $config): void
    {
        $path = $config['path'];
        $key = $config['key'];

        // Check if published config exists
        $publishedPath = config_path($key . '.php');

        if (file_exists($publishedPath)) {
            // Load the published config
            $publishedConfig = require $publishedPath;
            $packageConfig = require $path;

            // Merge with the package config, giving priority to the published config
            $mergedConfig = array_replace_recursive($packageConfig, $publishedConfig);

            // Set the merged config
            config([$key => $mergedConfig]);
        } else {
            // If no published config exists, just load the package config
            $packageConfig = require $path;
            config([$key => $packageConfig]);
        }
    }
}
