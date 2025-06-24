<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\SendOrderToIikoJob;
use App\Models\OrderStatus;

class ProcessOrderIIko
{
    /**
     * @param \App\Events\OrderStatusChanged $event
     *
     * @throws \Throwable
     */
    public function handle(OrderStatusChanged $event)
    {
        if ($event->newStatusId !== OrderStatus::ID_COLLECTED) {
            return;
        }

        if (! config('app.order.iiko.enable_sending')) {
            return;
        }

        SendOrderToIikoJob::dispatch($event->order);
    }
}
