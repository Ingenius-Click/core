<?php

namespace Ingenius\Core\Interfaces;

interface StoreConfigurationInterface
{
    /**
     * Get the configuration key.
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * Get the configuration value.
     *
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Get the package name that provides this configuration.
     *
     * @return string
     */
    public function getPackageName(): string;

    /**
     * Get the priority for this configuration (higher number = higher priority).
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Check if this configuration is available/enabled.
     *
     * @return bool
     */
    public function isAvailable(): bool;
}
