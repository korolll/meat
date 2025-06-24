<?php

namespace App\Services\Management\Client\Order;

interface OrderLockerInterface
{
    /**
     * @param string   $orderUuid
     * @param \Closure $callback
     *
     * @throws \Throwable
     * @return mixed
     */
    public function lock(string $orderUuid, \Closure $callback);
}
