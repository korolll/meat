<?php

namespace App\Services\Management\Client\Product;

use App\Models\Client;
use Carbon\CarbonInterface;
use Closure;

/**
 * This interface is needed for calculate totals of products
 */
interface ClientProductCollectionPriceCalculatorInterface
{
    /**
     * @param CalculateContextInterface   $ctx
     * @param array<ProductItemInterface> $productItems
     * @param \Closure|null               $productItemPriceCalculated
     *
     * @return \App\Services\Management\Client\Product\CollectionPriceDataInterface
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function calculate(CalculateContextInterface $ctx, array $productItems, ?Closure $productItemPriceCalculated = null): CollectionPriceDataInterface;
}
