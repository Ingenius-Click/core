<?php

namespace Ingenius\Core\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ManageNotificationsFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'manage-notifications';
    }

    public function getName(): string
    {
        return 'Manage notifications';
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
