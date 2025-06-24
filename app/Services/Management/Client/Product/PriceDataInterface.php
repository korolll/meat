<?php

namespace App\Services\Management\Client\Product;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;

interface PriceDataInterface extends Arrayable, JsonSerializable
{
    public function getPriceWithDiscount(): float;

    public function getDiscount(): float;

    public function getTotalAmountWithDiscount(): float;

    public function getTotalDiscount(): float;

    public function getTotalWeight(): float;

    public function getTotalQuantity(): float;

    public function getDiscountModel(): ?Model;

    public function getTotalBonus(): int;

    public function getPaidBonus(): int;

    public function getFixedPaidBonus(): float;

    public function diff(PriceDataInterface $data): PriceDataInterface;
}
