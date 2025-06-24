<?php

namespace App\Services\Management\Client\Order;

use App\Exceptions\ClientExceptions\OrderTooManyBonusProvided;
use App\Models\Order;
use App\Services\Management\Client\Bonus\MaxBonusesCalculatorInterface;
use App\Services\Money\MoneyHelper;

class OrderFinalPriceResolver implements OrderFinalPriceResolverInterface
{
    /**
     * @var \App\Services\Management\Client\Order\OrderDeliveryPriceCalculatorInterface
     */
    private OrderDeliveryPriceCalculatorInterface $deliveryPriceCalculator;

    /**
     * @var \App\Services\Management\Client\Bonus\MaxBonusesCalculatorInterface
     */
    private MaxBonusesCalculatorInterface $bonusesCalculator;

    /**
     * @param \App\Services\Management\Client\Order\OrderDeliveryPriceCalculatorInterface $deliveryPriceCalculator
     * @param \App\Services\Management\Client\Bonus\MaxBonusesCalculatorInterface         $bonusesCalculator
     */
    public function __construct(
        OrderDeliveryPriceCalculatorInterface $deliveryPriceCalculator,
        MaxBonusesCalculatorInterface $bonusesCalculator)
    {
        $this->deliveryPriceCalculator = $deliveryPriceCalculator;
        $this->bonusesCalculator = $bonusesCalculator;
    }

    /**
     * @param \App\Models\Order $order
     * @param int               $bonusesToPay
     *
     * @return \App\Models\Order
     * @throws \App\Exceptions\ClientExceptions\OrderTooManyBonusProvided
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function resolve(Order $order, int $bonusesToPay = 0): Order
    {
        // Add others updater if you need here
        $this->updateDeliveryPriceIfNeed($order);
        $this->updateTotalWithoutBonusesIfNeed($order);

        $this->checkMaxBonusesToPayIfNeed($order, $bonusesToPay);
        $this->updateTotalWithBonusesIfNeed($order, $bonusesToPay);

        $this->updateBonusToCharge($order);
        return $order;
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return bool
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    protected function updateTotalWithoutBonusesIfNeed(Order $order): bool
    {
        $attributes = [
            'delivery_price',
            'total_price_for_products_with_discount',
        ];

        if ($order->exists && ! $order->isDirty($attributes)) {
            return false;
        }

        /**
         * This part should be synced with
         * @see \App\Services\Management\Client\Order\OrderPriceResolver::resolve()
         */
        $finalPrice = MoneyHelper::of($order->total_price_for_products_with_discount)
            ->plus($order->delivery_price ?: 0)
            ->plus($order->paid_bonus);

        $order->total_price = MoneyHelper::toFloat($finalPrice);
        return true;
    }

    /**
     * @param \App\Models\Order $order
     * @param int               $bonusesToPay
     *
     * @return bool
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    protected function updateTotalWithBonusesIfNeed(Order $order, int $bonusesToPay): bool
    {
        $attributes = [
            'total_price',
            'paid_bonus',
        ];

        $order->paid_bonus = $bonusesToPay;
        if (! $order->isDirty($attributes)) {
            return false;
        }

        $finalPrice = MoneyHelper::of($order->total_price)
            ->minus($order->paid_bonus);

        $order->total_price = MoneyHelper::toFloat($finalPrice);
        return true;
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return bool
     */
    protected function updateDeliveryPriceIfNeed(Order $order): bool
    {
        $attributes = [
            'order_payment_id',
            'order_delivery_type_id',
            'client_address_data',
            'total_price_for_products_with_discount'
        ];

        if ($order->exists && ! $order->isDirty($attributes)) {
            return false;
        }

        $order->delivery_price = $this->deliveryPriceCalculator->calculate($order);
        return true;
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return bool
     */
    protected function updateBonusToCharge(Order $order): bool
    {
        // If paid bonuses provided - do nothing
        if ($order->paid_bonus > 0) {
            $order->bonus_to_charge = 0;
            return false;
        }

        $order->bonus_to_charge = $order->total_bonus;
        return true;
    }

    /**
     * @param \App\Models\Order $order
     * @param int               $bonusesToPay
     *
     * @return bool
     * @throws \App\Exceptions\ClientExceptions\OrderTooManyBonusProvided
     */
    protected function checkMaxBonusesToPayIfNeed(Order $order, int $bonusesToPay): bool
    {
        $maxBonuses = $this->bonusesCalculator->calculate($order->total_price);
        $order->setVirtualValue(Order::VIRTUAL_ATTR_MAX_BONUS, $maxBonuses);

        if ($bonusesToPay <= 0) {
            return false;
        }

        if ($maxBonuses < $bonusesToPay) {
            throw new OrderTooManyBonusProvided($maxBonuses);
        }

        return true;
    }
}
