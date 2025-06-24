<?php

namespace App\Services\Management\Client\Product;

use App\Models\Client;
use Carbon\CarbonInterface;
use Closure;
use Exception;
use Illuminate\Support\Arr;

/**
 * This is the simplest realization (not much effective), when we calculate price for each product sequentially.
 * But in the future it can be replaced by some complicated query to calculate all prices at once query
 */
class SimpleClientBulkProductPriceCalculator implements ClientBulkProductPriceCalculatorInterface
{
    /**
     * @var \App\Services\Management\Client\Product\ClientProductPriceCalculatorInterface
     */
    private ClientProductPriceCalculatorInterface $priceCalculator;

    private bool $enablePreloading = true;
    private bool $enablePreloadingClear = true;

    /**
     * @param \App\Services\Management\Client\Product\ClientProductPriceCalculatorInterface $priceCalculator
     */
    public function __construct(ClientProductPriceCalculatorInterface $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * @inheritDoc
     */
    public function calculateBulk(CalculateContextInterface $ctx, array $productItems, ?Closure $productItemPriceCalculated = null): array
    {
        $result = [];
        $products = [];
        foreach ($productItems as $productItem) {
            if (! $productItem instanceof ProductItemInterface) {
                throw new Exception('Product must implement ProductItemInterface');
            }

            $products[] = $productItem->getProduct();
        }

        if ($this->enablePreloading) {
            $this->priceCalculator->preLoadDiscounts($ctx, $products);
        }

        foreach ($productItems as $key => $productItem) {
            $quantity = $productItem->getQuantity();
            $priceData = $this->priceCalculator->calculate(
                $ctx,
                $productItem->getProduct(),
                $quantity,
                $productItem->getPaidBonus()
            );
            if ($productItemPriceCalculated) {
                $productItemPriceCalculated($key, $productItem, $priceData);
            }

            $result[] = $priceData;
        }

        if ($this->enablePreloadingClear) {
            $this->priceCalculator->clearPreloadedDiscounts();
        }

        return $result;
    }

    public function adjustDiscountPreloadCaching(bool $enablePreloading, bool $enablePreloadingClear): void
    {
        $this->enablePreloading = $enablePreloading;
        $this->enablePreloadingClear = $enablePreloadingClear;
    }
}
