<?php

namespace App\Services\Storaging\Warehouse;

use App\Models\Product;
use App\Models\WriteOff;
use App\Services\Storaging\Warehouse\DataStructures\ProductQuantityUpdate;

class WriteOffWarehouseTransactionProducer extends AbstractWarehouseTransactionProducer
{
    /**
     * @var WriteOff
     */
    protected $model;

    /**
     * @return \Illuminate\Support\Collection|Product[]
     */
    public function getProducts()
    {
        return collect([$this->model->product]);
    }

    /**
     * @param Product $product
     * @return int
     */
    public function getProductQuantityDelta(Product $product)
    {
        return $this->model->quantity_delta;
    }

    /**
     * @param Product $product
     * @param ProductQuantityUpdate $quantityUpdate
     */
    public function processProductQuantityUpdate(Product $product, ProductQuantityUpdate $quantityUpdate)
    {
        $this->model->quantity_old = $quantityUpdate->old;
        $this->model->quantity_new = $quantityUpdate->new;
    }
}
