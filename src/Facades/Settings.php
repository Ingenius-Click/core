<?php

namespace Ingenius\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Ingenius\Core\Services\SettingsService;

/**
 * @method static mixed get(string $group, string $name, $default = null)
 * @method static \Ingenius\Core\Models\Settings set(string $group, string $name, $value, bool $encrypt = false)
 * @method static bool has(string $group, string $name)
 * @method static bool forget(string $group, string $name)
 * @method static array getAllInGroup(string $group)
 * @method static bool lock(string $group, string $name)
 * @method static bool unlock(string $group, string $name)
 * @method static bool isLocked(string $group, string $name)
 * @method static void clearCache()
 * 
 * @see \Ingenius\Core\Services\SettingsService
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SettingsService::class;
    }
}
