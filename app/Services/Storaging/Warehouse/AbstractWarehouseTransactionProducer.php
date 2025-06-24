<?php

namespace App\Services\Storaging\Warehouse;

use App\Models\Product;
use App\Models\WarehouseTransaction;
use App\Services\Storaging\Warehouse\Contracts\WarehouseTransactionFactoryContract;
use App\Services\Storaging\Warehouse\Contracts\WarehouseTransactionProducerContract;
use App\Services\Storaging\Warehouse\DataStructures\ProductQuantityUpdate;
use App\Services\Storaging\Warehouse\DataStructures\WarehouseTransactionReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractWarehouseTransactionProducer implements WarehouseTransactionProducerContract
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var WarehouseTransactionFactoryContract
     */
    protected $transactionFactory;

    /**
     * @param WarehouseTransactionFactoryContract $transactionFactory
     */
    public function __construct(WarehouseTransactionFactoryContract $transactionFactory)
    {
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @return WarehouseTransactionReference
     */
    public function getWarehouseTransactionReference()
    {
        return WarehouseTransactionReference::make($this->model->getMorphClass(), $this->model->getKey());
    }

    /**
     * @param Product $product
     * @param ProductQuantityUpdate $quantityUpdate
     */
    public function processProductQuantityUpdate(Product $product, ProductQuantityUpdate $quantityUpdate)
    {
        //
    }

    /**
     * @param Model $model
     * @return Collection|WarehouseTransaction[]
     */
    public function produce(Model $model)
    {
        $this->model = $model;

        return $this->transactionFactory->create($this);
    }
}
