<?php

namespace Ingenius\Core\Services;

use Ingenius\Core\Interfaces\StoreConfigurationInterface;

class StoreConfigurationManager
{
    /**
     * The registered store configurations.
     *
     * @var array<string, StoreConfigurationInterface>
     */
    protected array $configurations = [];

    /**
     * Register a store configuration.
     *
     * @param StoreConfigurationInterface $configuration
     * @return void
     */
    public function register(StoreConfigurationInterface $configuration): void
    {
        $this->configurations[$configuration->getKey()] = $configuration;
    }

    /**
     * Get all registered configurations.
     *
     * @return array<string, StoreConfigurationInterface>
     */
    public function getConfigurations(): array
    {
        return $this->configurations;
    }

    /**
     * Get all available configurations (only those that are available).
     *
     * @return array<string, StoreConfigurationInterface>
     */
    public function getAvailableConfigurations(): array
    {
        return array_filter($this->configurations, function (StoreConfigurationInterface $config) {
            return $config->isAvailable();
        });
    }

    /**
     * Get configurations for a specific package.
     *
     * @param string $packageName
     * @return array<string, StoreConfigurationInterface>
     */
    public function getConfigurationsByPackage(string $packageName): array
    {
        return array_filter($this->configurations, function (StoreConfigurationInterface $config) use ($packageName) {
            return $config->getPackageName() === $packageName;
        });
    }

    /**
     * Get a specific configuration by key.
     *
     * @param string $key
     * @return StoreConfigurationInterface|null
     */
    public function getConfiguration(string $key): ?StoreConfigurationInterface
    {
        return $this->configurations[$key] ?? null;
    }

    /**
     * Check if a configuration exists.
     *
     * @param string $key
     * @return bool
     */
    public function hasConfiguration(string $key): bool
    {
        return isset($this->configurations[$key]);
    }

    /**
     * Get all configuration values as an array, sorted by priority.
     *
     * @return array
     */
    public function getAllValues(): array
    {
        $availableConfigs = $this->getAvailableConfigurations();

        // Sort by priority (higher first)
        uasort($availableConfigs, function (StoreConfigurationInterface $a, StoreConfigurationInterface $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        $result = [];
        foreach ($availableConfigs as $config) {
            $result[$config->getKey()] = $config->getValue();
        }

        return $result;
    }

    /**
     * Get all available package names.
     *
     * @return array
     */
    public function getAvailablePackages(): array
    {
        $packages = [];
        foreach ($this->getAvailableConfigurations() as $config) {
            $packages[] = $config->getPackageName();
        }

        return array_unique($packages);
    }
}
