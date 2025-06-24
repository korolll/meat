<?php

namespace App\Services\Storaging\Warehouse;

use App\Models\Catalog;
use App\Models\Product;
use App\Models\ProductRequests\CustomerProductRequest;
use App\Models\User;
use App\Services\Management\Product\Contracts\ProductReplicatorContract;
use App\Services\Storaging\Catalog\Contracts\DefaultCatalogFinderContract;
use App\Services\Storaging\Warehouse\Contracts\WarehouseTransactionFactoryContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerProductRequestWarehouseTransactionProducer extends AbstractWarehouseTransactionProducer
{
    /**
     * @var CustomerProductRequest
     */
    protected $model;

    /**
     * @var DefaultCatalogFinderContract
     */
    protected $catalogFinder;

    /**
     * @var ProductReplicatorContract
     */
    protected $productReplicator;

    /**
     * @var User
     */
    protected $recipient;

    /**
     * @var Catalog
     */
    protected $catalog;

    /**
     * @var array
     */
    protected $attributes = [
        'quantity' => 0,
        'quantum' => 1,
        'min_quantum_in_order' => 1,
        'price' => null,
    ];

    /**
     * @var Collection|Product[]
     */
    protected $replicaRelations;

    /**
     * @param WarehouseTransactionFactoryContract $transactionFactory
     * @param DefaultCatalogFinderContract $catalogFinder
     * @param ProductReplicatorContract $productReplicator
     */
    public function __construct(
        WarehouseTransactionFactoryContract $transactionFactory,
        DefaultCatalogFinderContract $catalogFinder,
        ProductReplicatorContract $productReplicator
    ) {
        parent::__construct($transactionFactory);

        $this->catalogFinder = $catalogFinder;
        $this->productReplicator = $productReplicator;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts()
    {
        $this->replicaRelations = Collection::make();

        return DB::transaction(function () {
            $this->recipient = $this->model->customerUser()->lockForUpdate()->first();
            $this->catalog = $this->catalogFinder->find($this->recipient);

            return $this->replicateProducts();
        });
    }

    /**
     * @param Product $product
     * @return int
     */
    public function getProductQuantityDelta(Product $product)
    {
        $product = $this->replicaRelations->get($product->uuid);

        return $product->pivot->quantity_actual;
    }

    /**
     * @return Collection|Product[]
     */
    protected function replicateProducts()
    {
        $replicas = Collection::make();

        foreach ($this->model->products as $product) {
            $replica = $this->productReplicator->replicate(
                $product,
                $this->recipient,
                $this->catalog,
                $this->attributes
            );

            $replicas->push($replica);

            // Мы должны помнить отношение реплики к оригиналу
            $this->replicaRelations->put($replica->uuid, $product);
        }

        return $replicas;
    }
}
