<?php

namespace App\Services\Management\Client\Order;

use App\Models\Order;

interface OrderFinalPriceResolverInterface
{
    public function resolve(Order $order, int $bonusesToPay = 0): Order;
}
