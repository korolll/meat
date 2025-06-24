<?php

namespace App\Services\Management\Client\Product\Discount\Concrete;

use App\Models\Order;
use App\Models\Product;
use App\Models\Promocode;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Management\Client\Product\Discount\AbstractClientProductDiscountResolverWithCache;
use App\Services\Management\Client\Product\Discount\DiscountData;
use App\Services\Money\MoneyHelper;
use Brick\Money\Exception\MoneyMismatchException;

class PromocodeDiscountResolver extends AbstractClientProductDiscountResolverWithCache
{
    private static float $totalProductItemsPrice = 0;

    private static ?string $promocode = null;

    public static function setTotalProductItemsPrice(float $totalProductItemsPrice): void
    {
        self::$totalProductItemsPrice = $totalProductItemsPrice;
    }

    public static function getTotalProductItemsPrice(): float
    {
        return self::$totalProductItemsPrice;
    }

    public static function setPromocode(?string $promocode): void
    {
        self::$promocode = $promocode;
    }

    public static function getPromocode(): ?string
    {
        return self::$promocode;
    }

    /**
     * @param CalculateContextInterface $ctx
     * @param iterable<Product> $products
     *
     * @return array<string, DiscountData>
     * @throws MoneyMismatchException
     */
    protected function findDiscountsValidProducts(CalculateContextInterface $ctx, iterable $products): array
    {
        if (!self::$promocode) {
            return $this->makeNullDiscountMap($products);
        }

        $orderExists = Order::where('client_uuid', $ctx->getClient()->uuid)
            ->where('promocode', self::$promocode)
            ->exists();

        $promocodeModel = Promocode::activeAt($ctx->getMoment())
            ->where('name', self::$promocode)
            ->where('enabled', true)
            ->first();

        if ($orderExists || ! $promocodeModel) {
            self::setPromocode(null);
            return $this->makeNullDiscountMap($products);
        }

        if ($promocodeModel->min_price > self::$totalProductItemsPrice) {
            self::setPromocode(null);
            return $this->makeNullDiscountMap($products);
        }

        $result = [];
        foreach ($products as $product) {
            $newPrice = MoneyHelper::valueWithDiscount($promocodeModel->discount_percent, $product->price);
            $newPrice = MoneyHelper::toFloat($newPrice);
            $result[$product->uuid] = new DiscountData($newPrice, $promocodeModel);
        }

        return $result;
    }
}
