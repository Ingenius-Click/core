<?php

namespace Ingenius\Core\Interfaces;

interface StockAvailabilityInterface
{
    /**
     * Get the real available stock for a product, accounting for reservations.
     * Returns null if the product doesn't manage stock or has unlimited stock.
     */
    public function getAvailableStock(IInventoriable $product): ?float;

    /**
     * Check if a product has enough available stock for the requested quantity.
     */
    public function hasAvailableStock(IInventoriable $product, float $quantity): bool;

    /**
     * Invalidate the cached available stock for a specific product.
     */
    public function invalidateCache(string $productibleType, int $productibleId): void;

    /**
     * Get cache hit/miss statistics for the current request lifecycle.
     */
    public function getStats(): array;
}
