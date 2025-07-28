<?php

namespace Ingenius\Core\Policies;

class SettingsPolicy
{
    public function view($user): bool
    {
        $userClass = tenant_user_class();

        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can('view_settings');
        }

        return false;
    }

    public function edit($user): bool
    {
        $userClass = tenant_user_class();

        if ($user && is_object($user) && is_a($user, $userClass)) {
            return $user->can('edit_settings');
        }

        return false;
    }
}
