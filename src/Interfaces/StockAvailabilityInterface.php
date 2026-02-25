<?php

namespace Ingenius\Core\Interfaces;

interface StockAvailabilityInterface
{
    /**
     * Get the real available stock for a product, accounting for reservations.
     * Returns null if the product doesn't manage stock or has unlimited stock.
     *
     * Pass $context with exclusion keys to skip specific reservations:
     *   - exclude_cart_owner_id + exclude_cart_owner_type: skip authenticated user's cart
     *   - exclude_cart_guest_token: skip guest's cart
     */
    public function getAvailableStock(IInventoriable $product, array $context = []): ?float;

    /**
     * Check if a product has enough available stock for the requested quantity.
     *
     * @see getAvailableStock for supported $context keys.
     */
    public function hasAvailableStock(IInventoriable $product, float $quantity, array $context = []): bool;

    /**
     * Invalidate the cached available stock for a specific product.
     */
    public function invalidateCache(string $productibleType, int $productibleId): void;

    /**
     * Get cache hit/miss statistics for the current request lifecycle.
     */
    public function getStats(): array;
}
