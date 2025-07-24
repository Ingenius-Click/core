<?php

namespace Ingenius\Core\Bootstrappers;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Spatie\Permission\PermissionRegistrar;

class SpatiePermissionsBootstrapper implements TenancyBootstrapper
{
    public function __construct(
        protected PermissionRegistrar $registrar,
    ) {}

    public function bootstrap(Tenant $tenant): void
    {
        // Update the cache key in the config
        $key = 'spatie.permission.cache.tenant.' . $tenant->getTenantKey();
        Config::set('permission.cache.key', $key);

        // Store the central model classes before overriding them
        app()->singleton('central.permission.models', function () {
            return [
                'permission' => Config::get('permission.models.permission'),
                'role' => Config::get('permission.models.role'),
            ];
        });

        // Update the model classes to use tenant models
        Config::set('permission.models.permission', \Ingenius\Auth\Models\Permission::class);
        Config::set('permission.models.role', \Ingenius\Auth\Models\Role::class);

        // Update the PermissionRegistrar directly using the injected instance
        $this->registrar->setPermissionClass(\Ingenius\Auth\Models\Permission::class);
        $this->registrar->setRoleClass(\Ingenius\Auth\Models\Role::class);
        $this->registrar->forgetCachedPermissions();
    }

    public function revert(): void
    {
        // Reset to the default cache key
        Config::set('permission.cache.key', 'spatie.permission.cache');

        // Restore the central model classes if they were stored
        if (App::has('central.permission.models')) {
            $centralModels = App::make('central.permission.models');
            Config::set('permission.models.permission', $centralModels['permission']);
            Config::set('permission.models.role', $centralModels['role']);

            // Update the PermissionRegistrar using the injected instance
            $this->registrar->setPermissionClass($centralModels['permission']);
            $this->registrar->setRoleClass($centralModels['role']);
            $this->registrar->forgetCachedPermissions();
        }
    }
}
