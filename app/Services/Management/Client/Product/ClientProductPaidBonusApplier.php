<?php

namespace App\Services\Management\Client\Product;

use App\Services\Money\MoneyHelper;
use Brick\Money\RationalMoney;

class ClientProductPaidBonusApplier implements ClientProductPaidBonusApplierInterface
{
    /**
     * @inheritDoc
     */
    public function apply(array $productItems, array $productItemsPriceData, int $bonuses): void
    {
        /**
         * @var float                $totalPrice
         * @var array<RationalMoney> $simplePrices
         */
        list($totalPrice, $simplePrices) = $this->calcTotalPrice($productItemsPriceData);

        $totalBonusesToApply = 0;
        $pricesArr = [];
        foreach ($productItems as $key => $productItem) {
            $priceForProduct = $simplePrices[$key];
            $pricesArr[$productItem->getProduct()->uuid] = MoneyHelper::toFloat($priceForProduct);

            $bonusesToApply = $priceForProduct
                ->dividedBy($totalPrice)
                ->multipliedBy($bonuses);

            $bonusesToApply = MoneyHelper::toBonus($bonusesToApply);
            $totalBonusesToApply += $bonusesToApply;

            $productItem->setPaidBonus($bonusesToApply);
        }

        if ($totalBonusesToApply == $bonuses) {
            return;
        }

        $diff = $totalBonusesToApply - $bonuses;
        $mod = $diff > 0 ? -1 : 1;

        $diff = abs($diff);

        usort($productItems, function (ProductItemInterface $productItem1, ProductItemInterface $productItem2) use ($pricesArr) {
            $v1 = $productItem1->getPaidBonus();
            $v2 = $productItem2->getPaidBonus();

            if (! $v1 && ! $v2) {
                $v1 = $pricesArr[$productItem1->getProduct()->uuid];
                $v2 = $pricesArr[$productItem2->getProduct()->uuid];
            }

            return -($v1 <=> $v2);
        });

        foreach ($productItems as $productItem) {
            $paidBonus = $productItem->getPaidBonus();
            $productItem->setPaidBonus($paidBonus + $mod);
            $diff--;
            if ($diff <= 0) {
                break;
            }
        }
    }


    /**
     * @param array<\App\Services\Management\Client\Product\PriceDataInterface> $productItemsPriceData
     *
     * @return array
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    protected function calcTotalPrice(array $productItemsPriceData): array
    {
        $totalPrice = MoneyHelper::of(0);
        $simplePrices = [];
        foreach ($productItemsPriceData as $key => $priceData) {
            $totalProductPrice = $priceData->getTotalAmountWithDiscount();
            $paidBonus = $priceData->getPaidBonus();

            if ($paidBonus) {
                $totalProductPriceWithoutBonus = MoneyHelper::of($totalProductPrice)->minus($paidBonus);
                $simplePrices[$key] = $totalProductPriceWithoutBonus;
                $totalProductPriceWithoutBonus = MoneyHelper::toFloat($totalProductPriceWithoutBonus);
            } else {
                $simplePrices[$key] = MoneyHelper::of($totalProductPrice);
                $totalProductPriceWithoutBonus = $totalProductPrice;
            }

            $totalPrice = $totalPrice->plus($totalProductPriceWithoutBonus);
        }

        return [MoneyHelper::toFloat($totalPrice), $simplePrices];
    }
}
