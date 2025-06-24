<?php

namespace App\Services\Storaging\Warehouse;

use App\Models\Product;
use App\Models\WarehouseTransaction;
use App\Services\Storaging\Warehouse\Contracts\WarehouseTransactionFactoryContract;
use App\Services\Storaging\Warehouse\Contracts\WarehouseTransactionProducerContract;
use App\Services\Storaging\Warehouse\DataStructures\ProductQuantityUpdate;
use App\Services\Storaging\Warehouse\DataStructures\WarehouseTransactionReference;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WarehouseTransactionFactory implements WarehouseTransactionFactoryContract
{
    const PRECISION = 0.001;

    /**
     * @var WarehouseTransactionProducerContract
     */
    protected $producer;

    /**
     * @var WarehouseTransactionReference
     */
    protected $transactionReference;

    /**
     * @param WarehouseTransactionProducerContract $producer
     * @return Collection|WarehouseTransaction[]
     */
    public function create(WarehouseTransactionProducerContract $producer)
    {
        $this->producer = $producer;
        $this->transactionReference = $producer->getWarehouseTransactionReference();

        return DB::transaction(function () {
            $products = $this->getProducts();
            if (! $products) {
                return null;
            }

            $transactions = $products->map(function (Product $product) {
                return $this->processProduct($product);
            });

            return $transactions->filter();
        });
    }

    /**
     * @return Collection|Product[]|null
     */
    protected function getProducts()
    {
        $products = $this->producer->getProducts();
        if (! $products instanceof Collection) {
            $products = collect($products);
        }

        if ($products->isEmpty()) {
            return null;
        }

        Product::lockForUpdate()->whereIn('uuid', $products->pluck('uuid'))->get(['uuid']);
        return $products;
    }

    /**
     * @param Product $product
     * @return WarehouseTransaction|null
     */
    protected function processProduct(Product $product)
    {
        $quantityUpdate = $this->makeProductQuantityUpdate($product);

        $this->processProductQuantityUpdate($product, $quantityUpdate);
        $this->producer->processProductQuantityUpdate($product, $quantityUpdate);

        return $this->createTransaction($product, $quantityUpdate);
    }

    /**
     * @param Product $product
     * @return ProductQuantityUpdate
     */
    protected function makeProductQuantityUpdate(Product $product)
    {
        $delta = $this->producer->getProductQuantityDelta($product);

        $old = $product->quantity;
        $new = $product->quantity + $delta;

        return ProductQuantityUpdate::make($old, $delta, $new);
    }

    /**
     * @param Product $product
     * @param ProductQuantityUpdate $quantityUpdate
     */
    protected function processProductQuantityUpdate(Product $product, ProductQuantityUpdate $quantityUpdate)
    {
        $product->quantity = $quantityUpdate->new;
        $product->save();
    }

    /**
     * @param Product $product
     * @param ProductQuantityUpdate $quantityUpdate
     * @return WarehouseTransaction|null
     */
    protected function createTransaction(Product $product, ProductQuantityUpdate $quantityUpdate)
    {
        if (round(abs($quantityUpdate->delta), 3) <= static::PRECISION) {
            return null;
        }

        $transaction = new WarehouseTransaction();
        $transaction->product_uuid = $product->uuid;
        $transaction->quantity_old = $quantityUpdate->old;
        $transaction->quantity_delta = $quantityUpdate->delta;
        $transaction->quantity_new = $quantityUpdate->new;
        $transaction->reference_type = $this->transactionReference->type;
        $transaction->reference_id = $this->transactionReference->id;
        $transaction->save();

        return $transaction;
    }
}
