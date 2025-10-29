<?php

namespace Ingenius\Core\Policies;

use Ingenius\Core\Constants\TemplatePermissions;
use Ingenius\Core\Models\Template;

class TemplatePolicy
{
    public function viewAny($user): bool
    {
        $userClass = central_user_class();

        if ($user instanceof $userClass) {
            return $user->can(TemplatePermissions::TEMPLATE_VIEW_ANY);
        }

        return false;
    }

    public function update($user, Template $template): bool
    {
        $userClass = central_user_class();

        if ($user instanceof $userClass) {
            return $user->can(TemplatePermissions::TEMPLATE_UPDATE);
        }

        return false;
    }

    public function create($user): bool
    {
        $userClass = central_user_class();

        if ($user instanceof $userClass) {
            return $user->can(TemplatePermissions::TEMPLATE_UPDATE);
        }

        return false;
    }
}
