<?php

namespace App\Services\Management\Client\Order;

use App\Models\Client;
use App\Models\Order;

interface OrderPriceResolverInterface
{
    public function resolve(Order $order, int $bonusesToPay = 0): Order;
}
