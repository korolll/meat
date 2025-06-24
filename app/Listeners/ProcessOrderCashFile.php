<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\CreateOrderCashFile;
use App\Jobs\DeleteOrderCashFile;
use App\Models\OrderDeliveryType;
use App\Models\OrderPaymentType;
use App\Models\OrderStatus;

class ProcessOrderCashFile
{
    /**
     * @param OrderStatusChanged $event
     *
     * @throws \Throwable
     */
    public function handle(OrderStatusChanged $event)
    {
        $order = $event->order;
        // Only cash
        if ($order->order_payment_type_id !== OrderPaymentType::ID_CASH) {
            return;
        }

        // Only pickup
        if ($order->order_delivery_type_id !== OrderDeliveryType::ID_PICKUP) {
            return;
        }

        switch ($event->newStatusId) {
            case OrderStatus::ID_COLLECTED:
                CreateOrderCashFile::dispatch($order);
                break;
            case OrderStatus::ID_CANCELLED:
                DeleteOrderCashFile::dispatch($order);
                break;
        }
    }
}
