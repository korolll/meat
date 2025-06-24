<?php

namespace App\Services\Management\Client\Product;

use App\Models\Client;
use Carbon\CarbonInterface;
use Closure;

/**
 * This interface is needed for calculate price for each of products
 */
interface ClientBulkProductPriceCalculatorInterface
{
    /**
     * @param CalculateContextInterface $ctx
     * @param array<ProductItemInterface> $productItems
     * @param \Closure|null $productItemPriceCalculated
     *
     * @return array<PriceDataInterface>
     */
    public function calculateBulk(CalculateContextInterface $ctx, array $productItems, ?Closure $productItemPriceCalculated = null): array;

    public function adjustDiscountPreloadCaching(bool $enablePreloading, bool $enablePreloadingClear): void;
}
