<?php

namespace App\Listeners;

use App\Events\ReceiptReceived;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProduceReceiptRelatedWarehouseTransactions implements ShouldQueue
{
    /**
     * @param ReceiptReceived $event
     */
    public function handle(ReceiptReceived $event)
    {
        app('warehouse.transactions.providers.receipt')->produce($event->receipt);
    }
}
