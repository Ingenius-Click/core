<?php

namespace Ingenius\Core\Services;

use Illuminate\Support\Facades\Cache;
use Ingenius\Core\Models\Settings;
use Illuminate\Support\Facades\Crypt;

class SettingsService
{
    /**
     * Cache key prefix for settings
     */
    protected string $cachePrefix = 'settings_';

    /**
     * Whether to use cache for settings
     */
    protected bool $useCache = true;

    /**
     * Whether to encrypt sensitive settings
     */
    protected bool $useEncryption = true;

    /**
     * Get a setting value by group and name
     *
     * @param string $group The settings group
     * @param string $name The setting name
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed
     */
    public function get(string $group, string $name, $default = null)
    {
        $cacheKey = $this->getCacheKey($group, $name);

        if ($this->useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $setting = Settings::where('group', $group)
            ->where('name', $name)
            ->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->payload;

        if ($this->useCache) {
            Cache::put($cacheKey, $value);
        }

        return $value;
    }

    /**
     * Set a setting value
     *
     * @param string $group The settings group
     * @param string $name The setting name
     * @param mixed $value The setting value
     * @param bool $encrypt Whether to encrypt the value
     * @return Settings
     */
    public function set(string $group, string $name, $value, bool $encrypt = false)
    {
        $setting = Settings::updateOrCreate(
            [
                'group' => $group,
                'name' => $name,
            ],
            [
                'payload' => $encrypt && $this->useEncryption ? Crypt::encrypt($value) : $value,
            ]
        );

        if ($this->useCache) {
            $cacheKey = $this->getCacheKey($group, $name);
            Cache::put($cacheKey, $value);
        }

        return $setting;
    }

    /**
     * Check if a setting exists
     *
     * @param string $group The settings group
     * @param string $name The setting name
     * @return bool
     */
    public function has(string $group, string $name): bool
    {
        return Settings::where('group', $group)
            ->where('name', $name)
            ->exists();
    }

    /**
     * Delete a setting
     *
     * @param string $group The settings group
     * @param string $name The setting name
     * @return bool
     */
    public function forget(string $group, string $name): bool
    {
        $deleted = Settings::where('group', $group)
            ->where('name', $name)
            ->delete();

        if ($this->useCache) {
            $cacheKey = $this->getCacheKey($group, $name);
            Cache::forget($cacheKey);
        }

        return $deleted > 0;
    }

    /**
     * Get all settings in a group
     *
     * @param string $group The settings group
     * @return array
     */
    public function getAllInGroup(string $group): array
    {
        $settings = Settings::where('group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->name] = $setting->payload;
        }

        return $result;
    }

    /**
     * Lock a setting to prevent changes
     *
     * @param string $group The settings group
     * @param string $name The setting name
     * @return bool
     */
    public function lock(string $group, string $name): bool
    {
        return Settings::where('group', $group)
            ->where('name', $name)
            ->update(['locked' => true]) > 0;
    }

    /**
     * Unlock a setting
     *
     * @param string $group The settings group
     * @param string $name The setting name
     * @return bool
     */
    public function unlock(string $group, string $name): bool
    {
        return Settings::where('group', $group)
            ->where('name', $name)
            ->update(['locked' => false]) > 0;
    }

    /**
     * Check if a setting is locked
     *
     * @param string $group The settings group
     * @param string $name The setting name
     * @return bool
     */
    public function isLocked(string $group, string $name): bool
    {
        $setting = Settings::where('group', $group)
            ->where('name', $name)
            ->first();

        return $setting ? $setting->locked : false;
    }

    /**
     * Clear all settings cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        // This is a simple implementation. In a real-world scenario,
        // you might want to be more selective about which cache keys to clear
        Cache::flush();
    }

    /**
     * Get the cache key for a setting
     *
     * @param string $group The settings group
     * @param string $name The setting name
     * @return string
     */
    protected function getCacheKey(string $group, string $name): string
    {
        return $this->cachePrefix . $group . '_' . $name;
    }
}
