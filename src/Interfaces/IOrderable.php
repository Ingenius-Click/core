<?php

namespace Ingenius\Core\Interfaces;

use Modules\Orders\Models\Order;

interface IOrderable
{
    public function getOrderableId(): string|int;

    public function getOrderableCode(): string|int;

    public function getCurrency(): string;

    public function getBaseCurrency(): string;

    public function getExchangeRate(): float;

    public function getItems(): array;

    public function getItemsSubtotal(): int;

    public function getTotalAmount(): int;
    public function getBaseTotalAmount(): int;
    public function getCustomerName(): string;

    public function getCustomerEmail(): string;

    public function getCustomerPhone(): ?string;

    public function getCustomerAddress(): ?string;
}
