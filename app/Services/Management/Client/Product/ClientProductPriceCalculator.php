<?php

namespace App\Services\Management\Client\Product;

use App\Models\AssortmentUnit;
use App\Models\Client;
use App\Models\Product;
use App\Models\Promocode;
use App\Services\Debug\DebugDataCollector;
use App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverPreloadInterface;
use App\Services\Management\Client\Product\Discount\Concrete\PromocodeDiscountResolver;
use App\Services\Management\Client\Product\Discount\DiscountDataInterface;
use App\Services\Money\MoneyHelper;
use App\Services\Quantity\FloatHelper;
use Carbon\CarbonInterface;

class ClientProductPriceCalculator implements ClientProductPriceCalculatorInterface
{
    /**
     * @var \App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverPreloadInterface
     */
    private ClientProductDiscountResolverPreloadInterface $discountResolver;
    private bool $useDiscountCache = false;

    /** @var array<?DiscountDataInterface> */
    private array $discountDataCache = [];

    /**
     * @param \App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverPreloadInterface $discountResolver
     */
    public function __construct(ClientProductDiscountResolverPreloadInterface $discountResolver)
    {
        $this->discountResolver = $discountResolver;
    }

    /**
     * @param Client $client
     * @param \App\Models\Product $product
     * @param float $quantity
     * @param int|null $paidBonus
     *
     * @return \App\Services\Management\Client\Product\PriceDataInterface
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function calculate(
        CalculateContextInterface $ctx,
        Product $product,
        float $quantity,
        int $paidBonus = 0
    ): PriceDataInterface
    {
        $result = [];
        $rmPrice = MoneyHelper::of($product->price ?: 0);
        $quantity = FloatHelper::round($quantity);

        /** @var DebugDataCollector $debugCollection */
        $debugCollection = app(DebugDataCollector::class);
        $discountData = $debugCollection->measure('ClientProductPriceCalculator:calculate:resolve-discount', function () use ($ctx, $product) {
            if ($this->useDiscountCache) {
                if (array_key_exists($product->uuid, $this->discountDataCache)) {
                    $discountData = $this->discountDataCache[$product->uuid];
                } else {
                    $discountData = $this->discountResolver->resolve($ctx, $product);
                    $this->discountDataCache[$product->uuid] = $discountData;
                }
            } else {
                $discountData = $this->discountResolver->resolve($ctx, $product);
            }

            return $discountData;
        }, ['product_uuid' => $product->uuid]);

        if ($discountData && ! $discountData->getDiscountModel() instanceof Promocode) {
            PromocodeDiscountResolver::setPromocode(null);
        }

        if ($discountData) {
            $result['price_with_discount'] = $discountData->getPrice();
            $result['discount_model'] = $discountData->getDiscountModel();

            $discount = $rmPrice->minus($result['price_with_discount']);
            $result['discount'] = MoneyHelper::toFloat($discount);
            $result['total_discount'] = MoneyHelper::toFloat($discount->multipliedBy($quantity));
        } else {
            $result['price_with_discount'] = $product->price ?: 0;
        }

        $assortment = $product->assortment;
        if ($assortment->assortment_unit_id === AssortmentUnit::ID_KILOGRAM) {
            $weight = 1000;
        } else {
            $weight = $assortment->weight;
        }

        list($totalAmountWithDiscountBeforeBonus, $totalAmountWithDiscount, $fixedPaidBonus) = $this->calculatePricesWithBonus($result, $quantity, $paidBonus);
        $totalBonus = 0;
        if ($assortment->bonus_percent > 0) {
            $totalBonus = $totalAmountWithDiscountBeforeBonus
                ->multipliedBy($assortment->bonus_percent)
                ->dividedBy(100);
        }

        $result['total_weight'] = FloatHelper::round($quantity * $weight);
        $result['total_quantity'] = $quantity;
        $result['total_amount_with_discount'] = MoneyHelper::toFloat($totalAmountWithDiscount);
        $result['total_bonus'] = MoneyHelper::toBonus($totalBonus);
        $result['paid_bonus'] = $paidBonus;
        $result['fixed_paid_bonus'] = $fixedPaidBonus;

        return new PriceData($result);
    }

    /**
     * @param bool $use
     *
     * @return void
     */
    public function setUseDiscountCache(bool $use): void
    {
        $this->useDiscountCache = $use;
    }

    /**
     * @param array<?DiscountDataInterface> $cache
     *
     * @return void
     */
    public function setDiscountDataCache(array $cache): void
    {
        $this->discountDataCache = $cache;
    }

    /**
     * @param array $result
     * @param float $quantity
     * @param int $paidBonus
     *
     * @return array
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    protected function calculatePricesWithBonus(array &$result, float $quantity, int $paidBonus): array
    {
        $priceWithDiscount = MoneyHelper::of($result['price_with_discount']);
        $totalAmountWithDiscount = $priceWithDiscount->multipliedBy($quantity);
        if ($paidBonus <= 0) {
            return [$totalAmountWithDiscount, $totalAmountWithDiscount, $paidBonus];
        }

        $newTotal = $totalAmountWithDiscount->minus($paidBonus);
        if (!FloatHelper::isEqual($quantity, 0)) {
            $result['price_with_discount'] = MoneyHelper::toFloat($newTotal->dividedBy($quantity));
        }

        $newTotalChecked = MoneyHelper::of($result['price_with_discount'])->multipliedBy($quantity);
        $diff = $newTotalChecked->minus($newTotal);
        $diff = MoneyHelper::toFloat($diff);
        $fixedPaidBonus = $paidBonus - $diff;

        return [$totalAmountWithDiscount, $newTotalChecked, $fixedPaidBonus];
    }

    public function preLoadDiscounts(CalculateContextInterface $ctx, iterable $products): void
    {
        $this->discountResolver->preLoad($ctx, $products);
    }

    public function clearPreloadedDiscounts(): void
    {
        $this->discountResolver->clearPreloadedData();
    }
}
