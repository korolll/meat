<?php

namespace App\Services\Storaging\Warehouse;

use App\Models\Product;
use App\Models\ProductRequests\SupplierProductRequest;

class SupplierProductRequestWarehouseTransactionProducer extends AbstractWarehouseTransactionProducer
{
    /**
     * @var SupplierProductRequest
     */
    protected $model;

    /**
     * @return \Illuminate\Support\Collection|Product[]
     */
    public function getProducts()
    {
        return $this->model->products;
    }

    /**
     * @param Product $product
     * @return int
     */
    public function getProductQuantityDelta(Product $product)
    {
        return -1 * $product->pivot->quantity;
    }
}
