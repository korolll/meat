<?php

namespace App\Services\Management\Client\Order;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Services\Debug\DebugDataCollector;
use App\Services\Management\Client\Product\CalculateContext;
use App\Services\Management\Client\Product\ClientBulkProductPriceCalculatorInterface;
use App\Services\Management\Client\Product\ClientProductCollectionPriceCalculatorInterface;
use App\Services\Management\Client\Product\ClientProductPaidBonusApplierInterface;
use App\Services\Management\Client\Product\ClientProductPriceCalculatorInterface;
use App\Services\Management\Client\Product\Discount\Concrete\PromocodeDiscountResolver;
use App\Services\Management\Client\Product\PriceDataInterface;
use App\Services\Management\Client\Product\ProductItem;
use App\Services\Management\Client\Product\TargetEnum;

/**
 * We can change it to OrderProductsPriceResolver which will be used in
 * OrderPriceResolver(OrderProductsPriceResolver + OrderFinalPriceResolverInterface)
 */
class OrderPriceResolver implements OrderPriceResolverInterface
{
    /**
     * @var \App\Services\Management\Client\Product\ClientProductCollectionPriceCalculatorInterface
     */
    private ClientProductCollectionPriceCalculatorInterface $calculator;

    /**
     * @var \App\Services\Management\Client\Order\OrderFinalPriceResolverInterface
     */
    private OrderFinalPriceResolverInterface $orderFinalPriceResolver;

    /**
     * @var \App\Services\Management\Client\Product\ClientProductPaidBonusApplierInterface
     */
    private ClientProductPaidBonusApplierInterface $bonusApplier;

    /**
     * @param \App\Services\Management\Client\Product\ClientProductCollectionPriceCalculatorInterface $calculator
     * @param \App\Services\Management\Client\Order\OrderFinalPriceResolverInterface                  $orderFinalPriceResolver
     * @param \App\Services\Management\Client\Product\ClientProductPaidBonusApplierInterface          $bonusApplier
     */
    public function __construct(
        ClientProductCollectionPriceCalculatorInterface $calculator,
        OrderFinalPriceResolverInterface $orderFinalPriceResolver,
        ClientProductPaidBonusApplierInterface $bonusApplier
    )
    {
        $this->calculator = $calculator;
        $this->orderFinalPriceResolver = $orderFinalPriceResolver;
        $this->bonusApplier = $bonusApplier;
    }

    /**
     * @param \App\Models\Order $order
     * @param int               $bonusesToPay
     *
     * @return \App\Models\Order
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function resolve(Order $order, int $bonusesToPay = 0): Order
    {
        if ($order->relationLoaded('orderProducts')) {
            $orderProducts = $order->orderProducts;
        } else {
            // Prevent models
            $orderProducts = $order->orderProducts()->with('product')->get();
            $order->setRelation('orderProducts', $orderProducts);
        }

        $productItems = [];
        $productItemsTotalPrice = 0;
        foreach ($orderProducts as $orderProduct) {
            $productItemsTotalPrice += $orderProduct->product->price * $orderProduct->quantity;
            $productItems[] = ProductItem::create($orderProduct->product, $orderProduct->quantity);
        }

        PromocodeDiscountResolver::setTotalProductItemsPrice($productItemsTotalPrice);

        $priceDataMap = [];
        $closure = function ($key, ProductItem $productItem, PriceDataInterface $data) use ($orderProducts, &$priceDataMap) {
            /** @var OrderProduct $orderProduct */
            $orderProduct = $orderProducts[$key];
            $orderProduct->applyPriceData($data);

            $priceDataMap[$key] = $data;
        };

        /** @var ClientProductPriceCalculatorInterface $priceCalculator */
        $priceCalculator = app(ClientProductPriceCalculatorInterface::class);
        $priceCalculator->setUseDiscountCache(true);

        /** @var ClientBulkProductPriceCalculatorInterface $priceCalculator */
        $bulkProductCalculator = app(ClientBulkProductPriceCalculatorInterface::class);
        $bulkProductCalculator->adjustDiscountPreloadCaching(true, false);

        /** @var DebugDataCollector $debugCollection */
        $debugCollection = app(DebugDataCollector::class);
        $ctx = new CalculateContext(
            $order->client,
            TargetEnum::ORDER,
            $order->created_at
        );

        $data = $debugCollection->measure('OrderPriceResolver:calculate-product-prices', function () use ($ctx, $productItems, $closure) {
            return $this->calculator->calculate($ctx, $productItems, $closure);
        });

        $order->applyCollectionPriceData($data);
        $this->orderFinalPriceResolver->resolve($order, $bonusesToPay);

        if ($bonusesToPay > 0) {
            $this->bonusApplier->apply($productItems, $priceDataMap, $bonusesToPay);

            $bulkProductCalculator->adjustDiscountPreloadCaching(false, true);
            $data = $this->calculator->calculate($ctx, $productItems, $closure, false);
            $order->applyCollectionPriceData($data);
            $this->orderFinalPriceResolver->resolve($order, $bonusesToPay);
        }

        $priceCalculator->setUseDiscountCache(false);
        $priceCalculator->setDiscountDataCache([]);
        $bulkProductCalculator->adjustDiscountPreloadCaching(true, true);

        return $order;
    }
}
