<?php

namespace Ingenius\Core\Interfaces;

interface IInventoriable
{
    public function getStock(): ?float;
    public function addStock(float $amount): void;
    public function removeStock(float $amount): void;
    public function inStock(): bool;
    public function handleStock(): bool;
    public function hasEnoughStock(float $quantity): bool;
}
