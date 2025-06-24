<?php

namespace App\Services\Management\Client\Product;

use App\Models\Product;

interface ProductItemInterface
{
    public function getProduct(): Product;

    public function getQuantity(): float;

    public function getPaidBonus(): int;

    public function setPaidBonus(int $bonus): self;
}
