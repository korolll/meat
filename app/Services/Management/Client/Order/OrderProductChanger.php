<?php

namespace App\Services\Management\Client\Order;

use App\Events\OrderProductChanged;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Services\Management\Client\Product\CalculateContext;
use App\Services\Management\Client\Product\ClientProductPriceCalculatorInterface;
use App\Services\Management\Client\Product\CollectionPriceData;
use App\Services\Management\Client\Product\TargetEnum;
use App\Services\Money\MoneyHelper;
use App\Services\Quantity\FloatHelper;

class OrderProductChanger implements OrderProductChangerInterface
{
    /**
     * @var \App\Services\Management\Client\Product\ClientProductPriceCalculatorInterface
     */
    private ClientProductPriceCalculatorInterface $productPriceCalculator;

    /**
     * @var \App\Services\Management\Client\Order\OrderSyncUpdaterInterface
     */
    private OrderSyncUpdaterInterface $orderSyncUpdater;

    /**
     * @param \App\Services\Management\Client\Product\ClientProductPriceCalculatorInterface $productPriceCalculator
     * @param \App\Services\Management\Client\Order\OrderSyncUpdaterInterface               $orderSyncUpdater
     */
    public function __construct(ClientProductPriceCalculatorInterface $productPriceCalculator, OrderSyncUpdaterInterface $orderSyncUpdater)
    {
        $this->productPriceCalculator = $productPriceCalculator;
        $this->orderSyncUpdater = $orderSyncUpdater;
    }

    /**
     * @param float                    $newQuantity
     * @param \App\Models\OrderProduct $orderProduct
     *
     * @return \App\Models\OrderProduct
     * @throws \Throwable
     */
    public function updateProductQuantity(float $newQuantity, OrderProduct $orderProduct): OrderProduct
    {
        $newQuantity = FloatHelper::round($newQuantity);
        if (FloatHelper::isEqual($orderProduct->quantity, $newQuantity)) {
            return $orderProduct;
        }

        $closure = function () use ($orderProduct, $newQuantity) {
            $orderProduct->refresh();
            if (FloatHelper::isEqual($orderProduct->quantity, $newQuantity)) {
                return [null, $orderProduct];
            }

            $paidBonus = 0;
            if ($orderProduct->paid_bonus > 0 && $orderProduct->quantity > $newQuantity) {
                // Calc new bonus
                $paidBonus = $orderProduct->paid_bonus;
                $bonusPerQuantity = $paidBonus / $orderProduct->quantity;
                $paidBonus = (int)($newQuantity * $bonusPerQuantity);
                if ($paidBonus < 0) {
                    $paidBonus = 0;
                }
            }

            $oldPriceData = $orderProduct->getPriceData();
            $ctx = new CalculateContext(
                $orderProduct->order->client,
                TargetEnum::ORDER,
                $orderProduct->order->created_at
            );
            $newPriceData = $this->productPriceCalculator->calculate(
                $ctx,
                $orderProduct->product,
                $newQuantity,
                $paidBonus
            );

            $diff = $oldPriceData->diff($newPriceData);
            $orderProduct->applyPriceData($newPriceData);
            $orderProduct->saveOrFail();

            OrderProductChanged::dispatch($orderProduct, $oldPriceData->getTotalQuantity(), $newQuantity);
            return [$diff, $orderProduct];
        };

        return $this->saveOrder($orderProduct->order, $closure);
    }

    /**
     * @param array $attributes
     *
     * @return \App\Models\OrderProduct
     * @throws \Throwable
     */
    public function addProduct(array $attributes): OrderProduct
    {
        $attributes['quantity'] = FloatHelper::round($attributes['quantity']);
        $newProduct = new OrderProduct($attributes);
        $closure = function () use ($newProduct) {
            $ctx = new CalculateContext(
                $newProduct->order->client,
                TargetEnum::ORDER,
                $newProduct->order->created_at
            );
            $newPrices = $this->productPriceCalculator->calculate(
                $ctx,
                $newProduct->product,
                $newProduct->quantity
            );

            $newProduct->applyPriceData($newPrices);
            $newProduct->saveOrFail();

            OrderProductChanged::dispatch($newProduct, 0, $newProduct->quantity);
            return [$newPrices, $newProduct];
        };

        return $this->saveOrder($newProduct->order, $closure);
    }

    /**
     * @param \App\Models\Order $order
     * @param \Closure          $getDiffAndModel
     *
     * @return \App\Models\OrderProduct
     */
    protected function saveOrder(Order $order, \Closure $getDiffAndModel): OrderProduct
    {
        $resultProduct = null;
        $this->orderSyncUpdater->update($order, function (Order $lockedOrder) use ($getDiffAndModel, &$resultProduct) {
            $currentTotals = $lockedOrder->getCollectionPriceData();

            /** @var ?\App\Services\Management\Client\Product\PriceDataInterface $diff */
            list($diff, $resultProduct) = $getDiffAndModel();
            if ($diff === null) {
                return;
            }

            $newTotalDiscount = MoneyHelper::of($currentTotals->getTotalDiscount())->plus($diff->getTotalDiscount());
            $newTotalDiscount = MoneyHelper::toFloat($newTotalDiscount);

            $newTotalPriceWithDiscount = MoneyHelper::of($currentTotals->getTotalPriceWithDiscount())->plus($diff->getTotalAmountWithDiscount());
            $newTotalPriceWithDiscount = MoneyHelper::toFloat($newTotalPriceWithDiscount);

            $newQuantity = FloatHelper::round($currentTotals->getTotalQuantity() + $diff->getTotalQuantity());
            $newWeight = FloatHelper::round($currentTotals->getTotalWeight() + $diff->getTotalWeight());

            $newTotalBonus = $currentTotals->getTotalBonus() + $diff->getTotalBonus();
            $newPaidBonus = $currentTotals->getPaidBonus() + $diff->getPaidBonus();

            $collectionPrice = new CollectionPriceData([
                'total_discount' => $newTotalDiscount,
                'total_price_with_discount' => $newTotalPriceWithDiscount,
                'total_weight' => $newWeight,
                'total_quantity' => $newQuantity,
                'total_bonus' => $newTotalBonus,
                'paid_bonus' => $newPaidBonus,
            ]);

            $lockedOrder->applyCollectionPriceData($collectionPrice);
        });

        return $resultProduct;
    }
}
