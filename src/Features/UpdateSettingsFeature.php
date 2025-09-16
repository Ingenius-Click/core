<?php

namespace Ingenius\Core\Features;

use Ingenius\Core\Interfaces\FeatureInterface;
use Ingenius\Core\Models\Settings;

class UpdateSettingsFeature implements FeatureInterface
{
    public function getIdentifier(): string
    {
        return 'update-settings';
    }

    public function getName(): string
    {
        return __('Update settings');
    }

    public function getGroup(): string
    {
        return __('Core');
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
