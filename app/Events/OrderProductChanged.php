<?php

namespace App\Events;

use App\Models\OrderProduct;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderProductChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @var \App\Models\OrderProduct
     */
    public OrderProduct $orderProduct;

    /**
     * @var float
     */
    public float $oldQuantity;

    /**
     * @var float
     */
    public float $newQuantity;

    /**
     * @param \App\Models\OrderProduct $orderProduct
     * @param float                    $oldQuantity
     * @param float                    $newQuantity
     */
    public function __construct(OrderProduct $orderProduct, float $oldQuantity, float $newQuantity)
    {
        $this->orderProduct = $orderProduct;
        $this->oldQuantity = $oldQuantity;
        $this->newQuantity = $newQuantity;
    }
}
