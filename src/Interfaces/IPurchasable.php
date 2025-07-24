<?php

namespace Ingenius\Core\Interfaces;

interface IPurchasable
{
    public function getFinalPrice(): int;
    public function getRegularPrice(): int;
    public function getId(): int;
    public function getName(): string;
}
