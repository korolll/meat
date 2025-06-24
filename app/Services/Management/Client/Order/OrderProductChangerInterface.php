<?php

namespace App\Services\Management\Client\Order;

use App\Models\OrderProduct;

interface OrderProductChangerInterface
{
    public function updateProductQuantity(float $newQuantity, OrderProduct $orderProduct): OrderProduct;

    public function addProduct(array $attributes): OrderProduct;
}
