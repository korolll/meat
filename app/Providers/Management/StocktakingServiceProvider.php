<?php

namespace App\Providers\Management;

use App\Services\Management\Stocktaking\Contracts\ProductSynchronizerContract;
use App\Services\Management\Stocktaking\ProductSynchronizer;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class StocktakingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $bindings = [
        ProductSynchronizerContract::class => ProductSynchronizer::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->bindings);
    }
}
