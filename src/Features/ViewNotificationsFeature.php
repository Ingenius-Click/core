<?php

namespace Ingenius\Core\Features;

use Ingenius\Core\Interfaces\FeatureInterface;

class ViewNotificationsFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'view-notifications';
    }

    public function getName(): string
    {
        return 'View notifications';
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
        return true;
    }
}
