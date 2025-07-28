<?php

namespace Ingenius\Core\Policies;

use Ingenius\Core\Models\Tenant;

class TenantPolicy
{
    public function viewAny($user): bool
    {
        $userClass = central_user_class();

        if ($user instanceof $userClass) {
            return $user->can('system.tenants.view');
        }

        return false;
    }

    public function create($user): bool
    {
        $userClass = central_user_class();

        if ($user instanceof $userClass) {
            return $user->can('system.tenants.create');
        }

        return false;
    }

    public function edit($user, Tenant $tenant): bool
    {
        $userClass = central_user_class();

        if ($user instanceof $userClass) {
            return $user->can('system.tenants.edit');
        }

        return false;
    }
}
