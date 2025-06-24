<?php

namespace App\Services\Storaging\Warehouse;

use App\Models\Product;
use App\Models\Stocktaking;
use App\Services\Storaging\Warehouse\DataStructures\ProductQuantityUpdate;

class StocktakingWarehouseTransactionProducer extends AbstractWarehouseTransactionProducer
{
    /**
     * @var Stocktaking
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
        return $product->pivot->quantity_new - $product->quantity;
    }

    /**
     * @param Product $product
     * @param ProductQuantityUpdate $quantityUpdate
     */
    public function processProductQuantityUpdate(Product $product, ProductQuantityUpdate $quantityUpdate)
    {
        $this->model->products()->updateExistingPivot($product->uuid, [
            'quantity_old' => $quantityUpdate->old,
            'quantity_new' => $quantityUpdate->new,
        ]);
    }
}
