<?php

namespace App\Services\Management\Client\Order;

use App\Models\Order;
use Closure;

interface OrderSyncUpdaterInterface
{
    public function update(Order $order, Closure $updateFunction): Order;
}
