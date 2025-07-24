<?php

namespace Ingenius\Core\Interfaces;

interface HasFeature
{
    /**
     * Get the required feature identifier for this component.
     *
     * @return string
     */
    public function getRequiredFeature(): FeatureInterface;
}
