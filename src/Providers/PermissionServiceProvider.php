<?php

namespace Ingenius\Core\Providers;

use Ingenius\Core\Constants\CentralDashboardPermissions;
use Ingenius\Core\Constants\TemplatePermissions;
use Ingenius\Core\Support\PermissionsManager;
use Illuminate\Support\ServiceProvider;
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
    }

    /**
     * Register central permissions.
     */
    protected function registerCentralPermissions(PermissionsManager $permissionsManager): void
    {
        // Central system management permissions
        $permissionsManager->registerMany([
            'system.tenants.view' => 'View tenants',
            'system.tenants.create' => 'Create tenants',
            'system.tenants.edit' => 'Edit tenants',
            'system.tenants.delete' => 'Delete tenants',
            'system.users.view' => 'View system users',
            'system.users.create' => 'Create system users',
            'system.users.edit' => 'Edit system users',
            'system.users.delete' => 'Delete system users',
            'system.roles.view' => 'View system roles',
            'system.roles.create' => 'Create system roles',
            'system.roles.edit' => 'Edit system roles',
            'system.roles.delete' => 'Delete system roles',
            'system.permissions.view' => 'View system permissions',
            'system.permissions.assign' => 'Assign system permissions',
        ], 'System', 'central');

        $permissionsManager->registerMany([
            CentralDashboardPermissions::VIEW => 'View dashboard',
        ], 'System', 'central');

        // Template management permissions
        $permissionsManager->registerMany([
            TemplatePermissions::TEMPLATE_VIEW_ANY => 'View templates',
        ], 'Templates', 'central');
    }
}
