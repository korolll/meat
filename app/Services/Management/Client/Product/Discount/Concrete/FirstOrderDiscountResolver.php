<?php

namespace App\Services\Management\Client\Product\Discount\Concrete;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Promo\PromoDescriptionFirstOrder;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Management\Client\Product\Discount\AbstractClientProductDiscountResolverWithCache;
use App\Services\Management\Client\Product\Discount\DiscountData;
use App\Services\Management\Client\Product\TargetEnum;
use App\Services\Models\Assortment\BannedAssortmentCheckerInterface;
use App\Services\Money\MoneyHelper;
use Brick\Money\Exception\MoneyMismatchException;
use Illuminate\Support\Arr;

class FirstOrderDiscountResolver extends AbstractClientProductDiscountResolverWithCache
{

    /**
     * @var \App\Models\Promo\PromoDescriptionFirstOrder
     */
    private $discountModel;

    private array $config;

    /**
     *
     */
    public function __construct(BannedAssortmentCheckerInterface $bannedAssortmentChecker, array $config)
    {
        $this->discountModel = PromoDescriptionFirstOrder::find(PromoDescriptionFirstOrder::UUID);
        $this->checkBannedAssortments = true;
        $this->config = [
            'discount_percent' => (float)Arr::get($config, 'discount_percent', 15)
        ];

        parent::__construct($bannedAssortmentChecker);
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
        // Disabled?
        if ($this->config['discount_percent'] <= 0) {
            return $this->makeNullDiscountMap($products);
        }

        // Calculating for order?
        if ($ctx->getTarget() !== TargetEnum::ORDER) {
            return $this->makeNullDiscountMap($products);
        }

        // We need to find an in-progress/success order
        $orderExist = Order::whereClientUuid($ctx->getClient()->uuid)
            ->where('created_at', '<=', $ctx->getMoment() ?: now())
            ->whereNotIn('order_status_id', [
                OrderStatus::ID_CANCELLED,
                OrderStatus::ID_COLLECTING,
            ])
            ->exists();

        if ($orderExist) {
            return $this->makeNullDiscountMap($products);
        }

        $result = [];
        foreach ($products as $product) {
            $newPrice = MoneyHelper::valueWithDiscount($this->config['discount_percent'], $product->price);
            $newPrice = MoneyHelper::toFloat($newPrice);
            $result[$product->uuid] = new DiscountData($newPrice, $this->discountModel);
        }

        return $result;
    }
}
