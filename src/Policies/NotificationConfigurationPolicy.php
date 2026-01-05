<?php

namespace Ingenius\Core\Policies;

use Ingenius\Core\Constants\NotificationsPermissions;
use Ingenius\Core\Models\NotificationConfiguration;

class NotificationConfigurationPolicy
{
    /**
     * Determine whether the user can view any notification configurations.
     */
    public function viewAny($user): bool
    {
        $userClass = tenant_user_class();
        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can(NotificationsPermissions::NOTIFICATIONS_VIEW);
        }
        return false;
    }

    /**
     * Determine whether the user can view the notification configuration.
     */
    public function view($user, NotificationConfiguration $notificationConfiguration): bool
    {
        $userClass = tenant_user_class();
        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can(NotificationsPermissions::NOTIFICATIONS_VIEW);
        }
        return false;
    }

    /**
     * Determine whether the user can create notification configurations.
     */
    public function create($user): bool
    {
        $userClass = tenant_user_class();
        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can(NotificationsPermissions::NOTIFICATIONS_CREATE);
        }
        return false;
    }

    /**
     * Determine whether the user can update the notification configuration.
     */
    public function update($user, NotificationConfiguration $notificationConfiguration): bool
    {
        $userClass = tenant_user_class();
        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can(NotificationsPermissions::NOTIFICATIONS_EDIT);
        }
        return false;
    }

    /**
     * Determine whether the user can delete the notification configuration.
     */
    public function delete($user, NotificationConfiguration $notificationConfiguration): bool
    {
        $userClass = tenant_user_class();
        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can(NotificationsPermissions::NOTIFICATIONS_DELETE);
        }
        return false;
    }
}
