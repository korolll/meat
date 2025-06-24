<?php

namespace App\Services\Management\Client\Order;

use App\Models\Order;

interface OrderDeliveryPriceCalculatorInterface
{
    public function calculate(Order $order): float;
}
