<?php

namespace App\Services\Management\Client\Product;

use App\Models\Client;
use App\Models\Product;
use Carbon\CarbonInterface;

/**
 * This interface is needed for calculate price for one product
 */
interface ClientProductPriceCalculatorInterface
{
    public function calculate(
        CalculateContextInterface $ctx,
        Product $product,
        float $quantity,
        int $paidBonus = 0
    ): PriceDataInterface;

    public function preLoadDiscounts(CalculateContextInterface $ctx, iterable $products): void;

    public function clearPreloadedDiscounts(): void;

    public function setUseDiscountCache(bool $use): void;

    public function setDiscountDataCache(array $cache): void;
}
