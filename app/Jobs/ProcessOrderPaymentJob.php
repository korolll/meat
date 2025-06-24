<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Management\Client\Order\OrderLockerInterface;
use App\Services\Management\Client\Order\Payment\OrderPaymentProcessorInterface;
use App\Services\Management\Client\Order\Payment\PaymentStatusEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\SerializesModels;

class ProcessOrderPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public int $tries = 12;


    /**
     * @var \App\Models\Order
     */
    public Order $order;

    /**
     * @param \App\Models\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        $throttler = new ThrottlesExceptions(3, 3);
        $throttler
            ->backoff(1)
            ->by($this->order->uuid);
        return [$throttler];
    }

    /**
     * @param \App\Services\Management\Client\Order\Payment\OrderPaymentProcessorInterface $orderPaymentProcessor
     * @param \App\Services\Management\Client\Order\OrderLockerInterface                   $orderLocker
     *
     * @return void
     * @throws \Throwable
     */
    public function handle(OrderPaymentProcessorInterface $orderPaymentProcessor, OrderLockerInterface $orderLocker)
    {
        $orderLocker->lock($this->order->uuid, function (Order $lockedOrder) use ($orderPaymentProcessor) {
            $result = $orderPaymentProcessor->process($lockedOrder);
            if (! $result || $result->order_status === PaymentStatusEnum::CREATED || $result->order_status === PaymentStatusEnum::APPROVED) {
                $this->release(60); // retry after 1 minute
            }
        });
    }
}
