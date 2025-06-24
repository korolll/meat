<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Models\Order\CashFileControllerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateOrderCashFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public int $tries = 3;

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
        $cashFileController->generateFile($this->order);
    }
}
