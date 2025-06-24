<?php

namespace App\Services\Storaging\Warehouse\Contracts;

use App\Models\WarehouseTransaction;
use Illuminate\Support\Collection;

interface WarehouseTransactionFactoryContract
{
    /**
     * @param WarehouseTransactionProducerContract $producer
     * @return Collection|WarehouseTransaction[]
     */
    public function create(WarehouseTransactionProducerContract $producer);
}
