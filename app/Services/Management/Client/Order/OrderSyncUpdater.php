<?php

namespace App\Services\Management\Client\Order;

use App\Models\Order;
use Closure;

class OrderSyncUpdater implements OrderSyncUpdaterInterface
{
    /**
     * @var \App\Services\Management\Client\Order\OrderLockerInterface
     */
    private OrderLockerInterface $locker;

    /**
     * @var \App\Services\Management\Client\Order\OrderFinalPriceResolver
     */
    private OrderFinalPriceResolver $orderFinalPriceResolver;

    /**
     * @param \App\Services\Management\Client\Order\OrderLockerInterface    $locker
     * @param \App\Services\Management\Client\Order\OrderFinalPriceResolver $orderFinalPriceResolver
     */
    public function __construct(OrderLockerInterface $locker, OrderFinalPriceResolver $orderFinalPriceResolver)
    {
        $this->locker = $locker;
        $this->orderFinalPriceResolver = $orderFinalPriceResolver;
    }

    /**
     * @param \App\Models\Order $order
     * @param \Closure          $updateFunction
     *
     * @return \App\Models\Order
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Throwable
     */
    public function update(Order $order, Closure $updateFunction): Order
    {
        return $this->locker->lock($order->uuid, function (Order $lockedOrder) use ($updateFunction) {
            return $this->updateOrder($lockedOrder, $updateFunction);
        });
    }

    /**
     * @param \App\Models\Order $order
     * @param \Closure          $updateFunction
     *
     * @return \App\Models\Order
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Throwable
     */
    protected function updateOrder(Order $order, Closure $updateFunction)
    {
        $updateFunction($order);
        $this->orderFinalPriceResolver->resolve($order, $order->paid_bonus ?: 0);
        $order->saveOrFail();
        return $order;
    }
}
