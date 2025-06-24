<?php

namespace App\Services\Management\Client\Product;


/**
 * This interface is needed for calculate price for one product
 */
interface ClientProductPaidBonusApplierInterface
{
    /**
     * @param array<\App\Services\Management\Client\Product\ProductItemInterface> $productItems
     * @param array<\App\Services\Management\Client\Product\PriceDataInterface>   $productItemsPriceData
     * @param int                                                                 $bonuses
     *
     * @return void
     */
    public function apply(array $productItems, array $productItemsPriceData, int $bonuses): void;
}
