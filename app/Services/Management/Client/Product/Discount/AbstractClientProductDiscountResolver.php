<?php

namespace App\Services\Management\Client\Product\Discount;

use App\Models\Client;
use App\Models\Product;
use Carbon\CarbonInterface;

abstract class AbstractClientProductDiscountResolver
{
    /**
     * @param \App\Models\Product $product
     *
     * @return bool
     */
    protected function isProductValid(Product $product): bool
    {
        return (bool)$product->price;
    }
}
