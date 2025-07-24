<?php

namespace Ingenius\Core\Settings;

use Illuminate\Support\Facades\App;
use Ingenius\Core\Facades\Settings as SettingsFacade;
use ReflectionClass;
use ReflectionProperty;

abstract class Settings
{
    /**
     * Get the group name for the settings class.
     *
     * @return string
     */
    abstract public static function group(): string;

    /**
     * Get the properties that should be encrypted.
     *
     * @return array
     */
    public static function encrypted(): array
    {
        return [];
    }

    /**
     * Get the casts for the settings properties.
     *
     * @return array
     */
    public static function casts(): array
    {
        return [];
    }

    /**
     * Load settings values from the repository.
     *
     * @return self
     */
    public function load(): self
    {
        $group = static::group();
        $settings = SettingsFacade::getAllInGroup($group);

        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name = $property->getName();
            if (isset($settings[$name])) {
                $this->{$name} = $settings[$name];
            }
        }

        return $this;
    }

    /**
     * Save settings values to the repository.
     *
     * @return self
     */
    public function save(): self
    {
        $group = static::group();
        $encrypted = static::encrypted();

        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $this->{$name};
            $shouldEncrypt = in_array($name, $encrypted);

            SettingsFacade::set($group, $name, $value, $shouldEncrypt);
        }

        return $this;
    }

    /**
     * Create a new instance of the settings class.
     *
     * @return static
     */
    public static function make(): static
    {
        $instance = new static();
        return $instance->load();
    }

    /**
     * Create a fake instance of the settings class with the given values.
     *
     * @param array $values
     * @return static
     */
    public static function fake(array $values = []): static
    {
        $instance = new static();

        foreach ($values as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->{$key} = $value;
            }
        }

        return $instance;
    }
}
