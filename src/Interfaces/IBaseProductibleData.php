<?php

namespace Ingenius\Core\Interfaces;

interface IBaseProductibleData
{
    public function getSlug(): string;
    public function getSku(): string;
    public function getDescription(): string;
    public function images(): ?array;
}
