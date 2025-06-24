<?php

namespace App\Providers\Storaging;

use App\Services\Storaging\Warehouse\Contracts\WarehouseTransactionFactoryContract;
use App\Services\Storaging\Warehouse\CustomerProductRequestWarehouseTransactionProducer;
use App\Services\Storaging\Warehouse\ReceiptWarehouseTransactionProducer;
use App\Services\Storaging\Warehouse\StocktakingWarehouseTransactionProducer;
use App\Services\Storaging\Warehouse\SupplierProductRequestWarehouseTransactionProducer;
use App\Services\Storaging\Warehouse\WarehouseTransactionFactory;
use App\Services\Storaging\Warehouse\WriteOffWarehouseTransactionProducer;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class WarehouseServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $bindings = [
        'warehouse.transactions.providers.customer-product-request' => CustomerProductRequestWarehouseTransactionProducer::class,
        'warehouse.transactions.providers.receipt' => ReceiptWarehouseTransactionProducer::class,
        'warehouse.transactions.providers.stocktaking' => StocktakingWarehouseTransactionProducer::class,
        'warehouse.transactions.providers.supplier-product-request' => SupplierProductRequestWarehouseTransactionProducer::class,
        'warehouse.transactions.providers.write-off' => WriteOffWarehouseTransactionProducer::class,
        WarehouseTransactionFactoryContract::class => WarehouseTransactionFactory::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->bindings);
    }
}
