<?php

namespace App\Services\Management\Client\Product\Discount;

use Illuminate\Database\Eloquent\Model;

interface DiscountDataInterface
{
    /**
     * @return float
     */
    public function getPrice(): float;

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getDiscountModel(): Model;

    /**
     * @return bool
     */
    public function isHighPriority(): bool;
}
