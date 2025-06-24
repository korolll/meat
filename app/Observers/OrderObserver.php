<?php

namespace App\Observers;

use App\Events\OrderIsCreated;
use App\Events\OrderIsCreating;
use App\Events\OrderIsDone;
use App\Events\OrderPaymentTypeChanging;
use App\Events\OrderStatusChanged;
use App\Events\OrderStatusChanging;
use App\Jobs\SendOrderCheckToAtolJob;
use App\Models\Order;
use App\Models\OrderPaymentType;
use App\Models\OrderStatus;

class OrderObserver
{
    /**
     * @param \App\Models\Order $order
     *
     * @return void
     */
    public function creating(Order $order)
    {
        OrderIsCreating::dispatch($order);
    }

    /**
     * @param \App\Models\Order $order
     */
    public function updating(Order $order)
    {
        if ($order->isDirty('order_status_id')) {
            $original = $order->getOriginal('order_status_id');
            $new = $order->order_status_id;
            OrderStatusChanging::dispatch($order, $original, $new);
        }

        if ($order->isDirty('order_payment_type_id')) {
            $original = $order->getOriginal('order_payment_type_id');
            $new = $order->order_payment_type_id;
            OrderPaymentTypeChanging::dispatch($order, $original, $new);
        }
    }

    /**
     * @param \App\Models\Order $order
     */
    public function updated(Order $order)
    {
        if ($order->isDirty('order_status_id')) {
            $original = $order->getOriginal('order_status_id');
            $new = $order->order_status_id;
            OrderStatusChanged::dispatch($order, $original, $new);

            if ($new === OrderStatus::ID_DONE) {
                OrderIsDone::dispatch($order, $original);
            }
        }

        if ($order->isDirty('is_paid') && $order->order_payment_type_id === OrderPaymentType::ID_ONLINE) {
            if ($order->is_paid) {
                // Send sell-receipt
                SendOrderCheckToAtolJob::dispatch($order, false)->afterCommit();
            } else {
                // Send sell-refund-receipt
                SendOrderCheckToAtolJob::dispatch($order, false, true)->afterCommit();
            }
        }
    }

    /**
     * @param \App\Models\Order $order
     */
    public function created(Order $order)
    {
        OrderIsCreated::dispatch($order);
    }
}
