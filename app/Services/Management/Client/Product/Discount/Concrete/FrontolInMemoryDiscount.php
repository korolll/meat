<?php

namespace App\Services\Management\Client\Product\Discount\Concrete;

use App\Models\Client;
use App\Models\Product;
use App\Models\PromoDescription;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Management\Client\Product\Discount\AbstractClientProductDiscountResolver;
use App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverInterface;
use App\Services\Management\Client\Product\Discount\DiscountData;
use App\Services\Management\Client\Product\Discount\DiscountDataInterface;
use App\Services\Money\MoneyHelper;
use Carbon\CarbonInterface;

class FrontolInMemoryDiscount extends AbstractClientProductDiscountResolver implements ClientProductDiscountResolverInterface
{
    /**
     * @var array
     */
    private array $pricesByArticleAndIndex = [];

    /**
     * @var \App\Models\PromoDescription
     */
    private $discountModel;

    /**
     *
     */
    public function __construct()
    {
        $this->discountModel = PromoDescription::find(PromoDescription::VIRTUAL_FRONTOL_DISCOUNT_UUID);
    }

    /**
     * @param array $positions
     *
     * @return $this
     */
    public function setPositions(array $positions): self
    {
        $this->pricesByArticleAndIndex = [];
        foreach ($positions as $positionIndex => $position) {
            $newPrice = $this->checkPositionHasDiscountAndCountThePrice($position);
            if ($newPrice === null) {
                continue;
            }

            $this->pricesByArticleAndIndex[$position['id']][$positionIndex] = $newPrice;
        }

        return $this;
    }

    /**
     * @param CalculateContextInterface $ctx
     * @param Product $product
     *
     * @return DiscountDataInterface|null
     */
    public function resolve(CalculateContextInterface $ctx, Product $product): ?DiscountDataInterface
    {
        if (! $this->isProductValid($product) || ! $this->pricesByArticleAndIndex || $product->loyaltySystemIndexInCheck === null) {
            return null;
        }

        $assortment = $product->assortment;
        $article = $assortment->article;
        if (! isset($this->pricesByArticleAndIndex[$article])) {
            return null;
        }

        $newPriceByIndex = $this->pricesByArticleAndIndex[$article];
        $index = $product->loyaltySystemIndexInCheck;
        if (! isset($newPriceByIndex[$index])) {
            return null;
        }

        return new DiscountData($newPriceByIndex[$index], $this->discountModel, true);
    }

    /**
     * @param array $position
     *
     * @return float|null
     */
    protected function checkPositionHasDiscountAndCountThePrice(array $position): ?float
    {
        $price = MoneyHelper::of($position['price']);
        $totalWithoutDiscount = $price->multipliedBy($position['quantity']);
        $totalWithDiscount = MoneyHelper::of($position['totalAmount']);
        $maxDiff = MoneyHelper::of(0.05);

        try {
            $diff = $totalWithoutDiscount->minus($totalWithDiscount);
            if ($diff->isZero() || $diff->isLessThan($maxDiff)) {
                return null;
            }

            $discountPerQuantity = $diff->dividedBy($position['quantity']);
            $newPrice = $price->minus($discountPerQuantity);
            return MoneyHelper::toFloat($newPrice);
        } catch (\Throwable $exception) {
            report($exception);
            return null;
        }
    }
}
