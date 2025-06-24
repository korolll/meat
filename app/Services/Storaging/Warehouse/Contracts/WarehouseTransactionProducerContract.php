<?php

namespace App\Services\Storaging\Warehouse\Contracts;

use App\Models\Product;
use App\Services\Storaging\Warehouse\DataStructures\ProductQuantityUpdate;
use App\Services\Storaging\Warehouse\DataStructures\WarehouseTransactionReference;
use Illuminate\Database\Eloquent\Model;

interface WarehouseTransactionProducerContract
{
    /**
     * @return WarehouseTransactionReference
     */
    public function getWarehouseTransactionReference();

    /**
     * @return \Illuminate\Support\Collection|Product[]
     */
    public function getProducts();

    /**
     * @param Product $product
     * @return int
     */
    public function getProductQuantityDelta(Product $product);

    /**
     * @param Product $product
     * @param ProductQuantityUpdate $quantityUpdate
     */
    public function processProductQuantityUpdate(Product $product, ProductQuantityUpdate $quantityUpdate);

    /**
     * @param Model $model
     */
    public function produce(Model $model);
}
