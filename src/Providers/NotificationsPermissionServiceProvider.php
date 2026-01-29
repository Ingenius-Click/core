<?php

namespace Ingenius\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Ingenius\Core\Constants\NotificationsPermissions;
use Ingenius\Core\Support\PermissionsManager;

class NotificationsPermissionServiceProvider extends ServiceProvider
{
    protected string $packageName = 'Notifications';

    /**
     * Bootstrap services.
     */
    public function boot(PermissionsManager $permissionsManager): void
    {
        $this->registerPermissions($permissionsManager);
    }

    /**
     * Register permissions for notifications
     */
    protected function registerPermissions(PermissionsManager $permissionsManager): void
    {
        $permissionsManager->register(
            NotificationsPermissions::NOTIFICATIONS_VIEW,
            'View notification configurations',
            $this->packageName,
            'tenant',
            __('core::permissions.display_names.view_notifications'),
            __('core::permissions.groups.notifications')
        );

        $permissionsManager->register(
            NotificationsPermissions::NOTIFICATIONS_CREATE,
            'Create notification configurations',
            $this->packageName,
            'tenant',
            __('core::permissions.display_names.create_notifications'),
            __('core::permissions.groups.notifications')
        );

        $permissionsManager->register(
            NotificationsPermissions::NOTIFICATIONS_EDIT,
            'Edit notification configurations',
            $this->packageName,
            'tenant',
            __('core::permissions.display_names.edit_notifications'),
            __('core::permissions.groups.notifications')
        );

        $permissionsManager->register(
            NotificationsPermissions::NOTIFICATIONS_DELETE,
            'Delete notification configurations',
            $this->packageName,
            'tenant',
            __('core::permissions.display_names.delete_notifications'),
            __('core::permissions.groups.notifications')
        );

        $permissionsManager->register(
            NotificationsPermissions::NOTIFICATIONS_TEMPLATES_VIEW,
            'View notification templates',
            $this->packageName,
            'tenant',
            __('core::permissions.display_names.view_templates'),
            __('core::permissions.groups.notifications')
        );

        $permissionsManager->register(
            NotificationsPermissions::NOTIFICATIONS_TEMPLATES_EDIT,
            'Edit notification templates',
            $this->packageName,
            'tenant',
            __('core::permissions.display_names.edit_templates'),
            __('core::permissions.groups.notifications')
        );
    }
}
