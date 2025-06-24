<?php

namespace App\Services\Management\Client\Product\Discount;

use App\Models\Client;
use App\Models\Product;
use App\Services\Management\Client\Product\CalculateContextInterface;
use Carbon\CarbonInterface;

interface ClientProductDiscountResolverInterface
{
    public function resolve(CalculateContextInterface $ctx, Product $product): ?DiscountDataInterface;
}
