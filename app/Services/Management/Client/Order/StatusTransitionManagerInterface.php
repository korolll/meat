<?php

namespace App\Services\Management\Client\Order;

use App\Models\Order;
use Illuminate\Contracts\Auth\Authenticatable;

interface StatusTransitionManagerInterface
{
    /**
     * @param \App\Models\Order                          $order
     * @param \Illuminate\Contracts\Auth\Authenticatable $caller
     * @param string                                     $nextStatusId
     *
     * @return \App\Models\Order
     */
    public function transition(Order $order, Authenticatable $caller, string $nextStatusId): Order;
}
