<?php

namespace Ingenius\Core\Interfaces;

interface FeatureInterface
{
    public function getIdentifier(): string;

    public function getName(): string;

    public function getGroup(): string;

    public function getPackage(): string;

    public function isBasic(): bool;
}
