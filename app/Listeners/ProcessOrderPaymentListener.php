<?php

namespace App\Listeners;

use App\Events\OrderPaymentTypeChanging;
use App\Events\OrderStatusChanging;
use App\Jobs\ProcessOrderPaymentJob;
use App\Models\OrderPaymentType;
use App\Models\OrderStatus;

class ProcessOrderPaymentListener
{
    /**
     * @param $event
     *
     * @throws \Throwable
     */
    public function handle($event)
    {
        $class = get_class($event);
        switch ($class) {
            case OrderStatusChanging::class:
                $this->handleOrderStatusChanging($event);
                break;
            case OrderPaymentTypeChanging::class:
                $this->handleOrderPaymentTypeChanging($event);
                break;
            default:
                throw new \Exception('Bad provided event type');
        }
    }

    /**
     * @param \App\Events\OrderStatusChanging $event
     *
     * @throws \Throwable
     */
    protected function handleOrderStatusChanging(OrderStatusChanging $event)
    {
        $order = $event->order;
        if ($order->order_payment_type_id !== OrderPaymentType::ID_ONLINE) {
            return;
        }

        switch ($event->newStatusId) {
            case OrderStatus::ID_COLLECTED:
            case OrderStatus::ID_CANCELLED:
                break;
            default:
                return;
        }

        ProcessOrderPaymentJob::dispatch($order)->afterCommit();
    }

    /**
     * @param \App\Events\OrderPaymentTypeChanging $event
     *
     * @throws \Throwable
     */
    protected function handleOrderPaymentTypeChanging(OrderPaymentTypeChanging $event)
    {
        $order = $event->order;
        if ($event->oldTypeId === OrderPaymentType::ID_ONLINE && $event->newTypeId === OrderPaymentType::ID_CASH) {
            return;
        }

        ProcessOrderPaymentJob::dispatch($order)->afterCommit();
    }
}
