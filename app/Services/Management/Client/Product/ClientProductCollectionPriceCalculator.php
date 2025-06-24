<?php

namespace App\Services\Management\Client\Product;

use App\Models\Client;
use App\Services\Money\MoneyHelper;
use App\Services\Quantity\FloatHelper;
use Carbon\CarbonInterface;
use Closure;

class ClientProductCollectionPriceCalculator implements ClientProductCollectionPriceCalculatorInterface
{
    /**
     * @var \App\Services\Management\Client\Product\ClientBulkProductPriceCalculatorInterface
     */
    private ClientBulkProductPriceCalculatorInterface $priceBulkCalculator;

    /**
     * @param \App\Services\Management\Client\Product\ClientBulkProductPriceCalculatorInterface $priceBulkCalculator
     */
    public function __construct(ClientBulkProductPriceCalculatorInterface $priceBulkCalculator)
    {
        $this->priceBulkCalculator = $priceBulkCalculator;
    }

    /**
     * @inheritDoc
     */
    public function calculate(CalculateContextInterface $ctx, array $productItems, ?Closure $productItemPriceCalculated = null): CollectionPriceDataInterface
    {
        $totalDiscount = MoneyHelper::of(0);
        $totalPriceWithDiscount = MoneyHelper::of(0);
        $totalWeight = 0;
        $totalQuantity = 0;
        $totalBonus = 0;
        $paidBonus = 0;

        $callback = function ($key, ProductItem $productItem, PriceDataInterface $priceData)
        use (
            $productItemPriceCalculated,
            &$totalDiscount,
            &$totalPriceWithDiscount,
            &$totalWeight,
            &$totalQuantity,
            &$totalBonus,
            &$paidBonus
        ) {
            if ($productItemPriceCalculated) {
                $productItemPriceCalculated($key, $productItem, $priceData);
            }

            $totalDiscount = $totalDiscount->plus($priceData->getTotalDiscount());
            $totalPriceWithDiscount = $totalPriceWithDiscount->plus($priceData->getTotalAmountWithDiscount());

            $totalWeight += $priceData->getTotalWeight();
            $totalQuantity += $priceData->getTotalQuantity();
            $totalBonus += $priceData->getTotalBonus();
            $paidBonus += $priceData->getPaidBonus();

            $totalQuantity = FloatHelper::round($totalQuantity);
            $totalWeight = FloatHelper::round($totalWeight);
        };

        $this->priceBulkCalculator->calculateBulk($ctx, $productItems, $callback);
        return new CollectionPriceData([
            'total_discount' => MoneyHelper::toFloat($totalDiscount),
            'total_price_with_discount' => MoneyHelper::toFloat($totalPriceWithDiscount),
            'total_weight' => $totalWeight,
            'total_quantity' => $totalQuantity,
            'total_bonus' => $totalBonus,
            'paid_bonus' => $paidBonus,
        ]);
    }
}
