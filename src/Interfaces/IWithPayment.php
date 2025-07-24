<?php

namespace Ingenius\Core\Interfaces;

interface IWithPayment
{
    public function onPaymentSuccess(?string $intendedStatus = null): void;

    public function onPaymentFailed(?string $intendedStatus = null): void;

    public function onPaymentExpired(?string $intendedStatus = null): void;
}
