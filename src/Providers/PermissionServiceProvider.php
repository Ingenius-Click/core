<?php

namespace Ingenius\Core\Providers;

use Ingenius\Core\Constants\CentralDashboardPermissions;
use Ingenius\Core\Constants\TemplatePermissions;
use Ingenius\Core\Support\PermissionsManager;
use Illuminate\Support\ServiceProvider;
use Ingenius\Core\Constants\SettingsPermissions;
use Ingenius\Core\Traits\RegistersConfigurations;

class PermissionServiceProvider extends ServiceProvider
{
    use RegistersConfigurations;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register permission config if it exists
        $configPath = __DIR__ . '/../../config/permission.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'permission');
            $this->registerConfig($configPath, 'permission', 'core');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(PermissionsManager $permissionsManager): void
    {
        $this->registerCentralPermissions($permissionsManager);
        $this->registerTenantPermissions($permissionsManager);
    }

    protected function registerTenantPermissions(PermissionsManager $permissionsManager): void
    {
        $permissionsManager->register(
            SettingsPermissions::VIEW_SETTINGS,
            'View settings',
            'Core',
            'tenant',
            'View settings',
            'Settings'
        );

        $permissionsManager->register(
            SettingsPermissions::EDIT_SETTINGS,
            'Edit settings',
            'Core',
            'tenant',
            'Edit settings',
            'Settings'
        );
    }

    /**
     * Register central permissions.
     */
    protected function registerCentralPermissions(PermissionsManager $permissionsManager): void
    {
        // Tenant management permissions
        $permissionsManager->register(
            'system.tenants.view',
            'View tenants',
            'Core',
            'central',
            'View tenants',
            'Tenants'
        );

        $permissionsManager->register(
            'system.tenants.create',
            'Create tenants',
            'Core',
            'central',
            'Create tenants',
            'Tenants'
        );

        $permissionsManager->register(
            'system.tenants.edit',
            'Edit tenants',
            'Core',
            'central',
            'Edit tenants',
            'Tenants'
        );

        $permissionsManager->register(
            'system.tenants.delete',
            'Delete tenants',
            'Core',
            'central',
            'Delete tenants',
            'Tenants'
        );

        // System users permissions
        $permissionsManager->register(
            'system.users.view',
            'View system users',
            'Core',
            'central',
            'View system users',
            'System Users'
        );

        $permissionsManager->register(
            'system.users.create',
            'Create system users',
            'Core',
            'central',
            'Create system users',
            'System Users'
        );

        $permissionsManager->register(
            'system.users.edit',
            'Edit system users',
            'Core',
            'central',
            'Edit system users',
            'System Users'
        );

        $permissionsManager->register(
            'system.users.delete',
            'Delete system users',
            'Core',
            'central',
            'Delete system users',
            'System Users'
        );

        // System roles permissions
        $permissionsManager->register(
            'system.roles.view',
            'View system roles',
            'Core',
            'central',
            'View system roles',
            'System Roles'
        );

        $permissionsManager->register(
            'system.roles.create',
            'Create system roles',
            'Core',
            'central',
            'Create system roles',
            'System Roles'
        );

        $permissionsManager->register(
            'system.roles.edit',
            'Edit system roles',
            'Core',
            'central',
            'Edit system roles',
            'System Roles'
        );

        $permissionsManager->register(
            'system.roles.delete',
            'Delete system roles',
            'Core',
            'central',
            'Delete system roles',
            'System Roles'
        );

        // System permissions management
        $permissionsManager->register(
            'system.permissions.view',
            'View system permissions',
            'Core',
            'central',
            'View system permissions',
            'System Permissions'
        );

        $permissionsManager->register(
            'system.permissions.assign',
            'Assign system permissions',
            'Core',
            'central',
            'Assign system permissions',
            'System Permissions'
        );

        // Dashboard permission
        $permissionsManager->register(
            CentralDashboardPermissions::VIEW,
            'View dashboard',
            'Core',
            'central',
            'View dashboard',
            'Dashboard'
        );

        // Template management permissions
        $permissionsManager->register(
            TemplatePermissions::TEMPLATE_VIEW_ANY,
            'View templates',
            'Core',
            'central',
            'View templates',
            'Templates'
        );

        $permissionsManager->register(
            TemplatePermissions::TEMPLATE_UPDATE,
            'Update templates',
            'Core',
            'central',
            'Update templates',
            'Templates'
        );
    }
}
