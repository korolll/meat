<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Models\Order\CashFileControllerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteOrderCashFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Order
     */
    protected Order $order;

    /**
     * @param \App\Models\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return void
     */
    public function handle(CashFileControllerInterface $cashFileController)
    {
        $cashFileController->deleteFile($this->order);
    }
}
