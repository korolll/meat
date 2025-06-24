<?php

namespace App\Providers\Models;

use App\Contracts\Models\Product\MakeProductsAvailableForRequestQueryContract;
use App\Services\Models\Product\MakeProductsAvailableForRequestQuery;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        MakeProductsAvailableForRequestQueryContract::class => MakeProductsAvailableForRequestQuery::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
