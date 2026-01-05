<?php

namespace Ingenius\Core\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ManageNotificationTemplatesFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'manage-notification-templates';
    }

    public function getName(): string
    {
        return 'Manage notification templates';
    }

    public function getGroup(): string
    {
        return 'Notifications';
    }

    public function getPackage(): string
    {
        return 'core';
    }

    public function isBasic(): bool
    {
        return false;
    }
}
