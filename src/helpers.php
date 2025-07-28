<?php

use Ingenius\Core\Services\SettingsService;

if (!function_exists('tenant')) {
    /**
     * Get the current tenant instance.
     *
     * @return \Ingenius\Core\Models\Tenant|null
     */
    function tenant()
    {
        return app(\Stancl\Tenancy\Tenancy::class)->tenant;
    }
}

if (!function_exists('tenancy')) {
    /**
     * Get the tenancy instance.
     *
     * @return \Stancl\Tenancy\Tenancy
     */
    function tenancy()
    {
        return app(\Stancl\Tenancy\Tenancy::class);
    }
}

if (!function_exists('is_tenant_route')) {
    /**
     * Check if the current route is a tenant route.
     *
     * @return bool
     */
    function is_tenant_route()
    {
        return tenant() !== null;
    }
}

if (!function_exists('settings')) {
    /**
     * Get the settings service instance.
     *
     * @param string|null $group The settings group
     * @param string|null $name The setting name
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed|\Ingenius\Core\Services\SettingsService
     */
    function settings(string $group = null, string $name = null, $default = null)
    {
        $settings = app(SettingsService::class);

        if (is_null($group)) {
            return $settings;
        }

        if (is_null($name)) {
            return $settings->getAllInGroup($group);
        }

        return $settings->get($group, $name, $default);
    }
}

if (!function_exists('central_user_class')) {
    /**
     * Get the central user model.
     *
     * @return \Ingenius\Core\Models\User
     */
    function central_user_class()
    {
        $userClass = config('core.central_user_model');

        return $userClass;
    }
}

if (!function_exists('tenant_user_class')) {
    /**
     * Get the tenant user model class.
     *
     * @return string
     */
    function tenant_user_class()
    {
        return config('core.tenant_user_model', 'Ingenius\\Auth\\Models\\User');
    }
}
