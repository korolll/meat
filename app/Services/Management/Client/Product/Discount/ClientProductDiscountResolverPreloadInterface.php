<?php

namespace App\Services\Management\Client\Product\Discount;

use App\Models\Client;
use App\Services\Management\Client\Product\CalculateContextInterface;
use Carbon\CarbonInterface;

interface ClientProductDiscountResolverPreloadInterface extends ClientProductDiscountResolverInterface
{
    /**
     * @param CalculateContextInterface $client
     * @param iterable<\App\Models\Product> $products
     *
     * @return void
     */
    public function preLoad(CalculateContextInterface $ctx, iterable $products): void;

    public function clearPreloadedData(): void;
}
