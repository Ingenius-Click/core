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
            __('core::permissions.display_names.view_settings'),
            __('core::permissions.groups.settings')
        );

        $permissionsManager->register(
            SettingsPermissions::EDIT_SETTINGS,
            'Edit settings',
            'Core',
            'tenant',
            __('core::permissions.display_names.edit_settings'),
            __('core::permissions.groups.settings')
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
            __('core::permissions.display_names.view_tenants'),
            __('core::permissions.groups.tenants')
        );

        $permissionsManager->register(
            'system.tenants.create',
            'Create tenants',
            'Core',
            'central',
            __('core::permissions.display_names.create_tenants'),
            __('core::permissions.groups.tenants')
        );

        $permissionsManager->register(
            'system.tenants.edit',
            'Edit tenants',
            'Core',
            'central',
            __('core::permissions.display_names.edit_tenants'),
            __('core::permissions.groups.tenants')
        );

        $permissionsManager->register(
            'system.tenants.delete',
            'Delete tenants',
            'Core',
            'central',
            __('core::permissions.display_names.delete_tenants'),
            __('core::permissions.groups.tenants')
        );

        // System users permissions
        $permissionsManager->register(
            'system.users.view',
            'View system users',
            'Core',
            'central',
            __('core::permissions.display_names.view_system_users'),
            __('core::permissions.groups.system_users')
        );

        $permissionsManager->register(
            'system.users.create',
            'Create system users',
            'Core',
            'central',
            __('core::permissions.display_names.create_system_users'),
            __('core::permissions.groups.system_users')
        );

        $permissionsManager->register(
            'system.users.edit',
            'Edit system users',
            'Core',
            'central',
            __('core::permissions.display_names.edit_system_users'),
            __('core::permissions.groups.system_users')
        );

        $permissionsManager->register(
            'system.users.delete',
            'Delete system users',
            'Core',
            'central',
            __('core::permissions.display_names.delete_system_users'),
            __('core::permissions.groups.system_users')
        );

        // System roles permissions
        $permissionsManager->register(
            'system.roles.view',
            'View system roles',
            'Core',
            'central',
            __('core::permissions.display_names.view_system_roles'),
            __('core::permissions.groups.system_roles')
        );

        $permissionsManager->register(
            'system.roles.create',
            'Create system roles',
            'Core',
            'central',
            __('core::permissions.display_names.create_system_roles'),
            __('core::permissions.groups.system_roles')
        );

        $permissionsManager->register(
            'system.roles.edit',
            'Edit system roles',
            'Core',
            'central',
            __('core::permissions.display_names.edit_system_roles'),
            __('core::permissions.groups.system_roles')
        );

        $permissionsManager->register(
            'system.roles.delete',
            'Delete system roles',
            'Core',
            'central',
            __('core::permissions.display_names.delete_system_roles'),
            __('core::permissions.groups.system_roles')
        );

        // System permissions management
        $permissionsManager->register(
            'system.permissions.view',
            'View system permissions',
            'Core',
            'central',
            __('core::permissions.display_names.view_system_permissions'),
            __('core::permissions.groups.system_permissions')
        );

        $permissionsManager->register(
            'system.permissions.assign',
            'Assign system permissions',
            'Core',
            'central',
            __('core::permissions.display_names.assign_system_permissions'),
            __('core::permissions.groups.system_permissions')
        );

        // Dashboard permission
        $permissionsManager->register(
            CentralDashboardPermissions::VIEW,
            'View dashboard',
            'Core',
            'central',
            __('core::permissions.display_names.view_dashboard'),
            __('core::permissions.groups.dashboard')
        );

        // Template management permissions
        $permissionsManager->register(
            TemplatePermissions::TEMPLATE_VIEW_ANY,
            'View templates',
            'Core',
            'central',
            __('core::permissions.display_names.view_templates'),
            __('core::permissions.groups.templates')
        );

        $permissionsManager->register(
            TemplatePermissions::TEMPLATE_UPDATE,
            'Update templates',
            'Core',
            'central',
            __('core::permissions.display_names.update_templates'),
            __('core::permissions.groups.templates')
        );
    }
}
