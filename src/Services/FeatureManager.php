<?php

namespace Ingenius\Core\Services;

use Ingenius\Core\Interfaces\FeatureInterface;

class FeatureManager
{
    protected array $features = [];

    public function register(FeatureInterface $feature): void
    {
        $this->features[$feature->getIdentifier()] = $feature;
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function getBasicFeatures(): array
    {
        return array_filter($this->features, fn(FeatureInterface $feature) => $feature->isBasic());
    }

    public function getFeature(string $identifier): FeatureInterface
    {
        return $this->features[$identifier];
    }

    public function getFeaturesByPackage(string $package): array
    {
        return array_filter($this->features, fn(FeatureInterface $feature) => $feature->getPackage() === $package);
    }
}
