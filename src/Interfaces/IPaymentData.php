<?php

namespace Ingenius\Core\Interfaces;

interface IPaymentData
{
    public function getAmount(): int;
    public function getCurrency(): string;
    public function getMetadata(): ?array;
    public function getPayformId(): string;
    public function getStatus(): string;
    public function getName(): string;
    public function needsFurtherActionRequired(): bool;
}
