<?php

namespace App\Services\Management\Client\Order;

use App\Models\Order;
use Closure;
use Illuminate\Support\Facades\DB;

class OrderLocker implements OrderLockerInterface
{
    protected array $lockedOrders = [];

    /**
     * @inheritDoc
     */
    public function lock(string $orderUuid, Closure $callback)
    {
        if (isset($this->lockedOrders[$orderUuid])) {
            return $callback($this->lockedOrders[$orderUuid]);
        }

        try {
            return DB::transaction(function () use ($orderUuid, $callback) {
                $this->lockedOrders[$orderUuid] = Order::lockForUpdate()
                    ->where('uuid', $orderUuid)
                    ->first();

                return $callback($this->lockedOrders[$orderUuid]);
            });
        } finally {
            unset($this->lockedOrders[$orderUuid]);
        }
    }
}
