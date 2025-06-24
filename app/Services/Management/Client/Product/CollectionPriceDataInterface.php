<?php

namespace App\Services\Management\Client\Product;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

interface CollectionPriceDataInterface extends Arrayable, JsonSerializable
{
    public function getTotalDiscount(): float;

    public function getTotalPriceWithDiscount(): float;

    public function getTotalWeight(): float;

    public function getTotalQuantity(): float;

    public function getTotalBonus(): int;

    public function getPaidBonus(): int;
}
