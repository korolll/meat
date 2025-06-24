<?php

namespace App\Providers\Storaging;

use App\Services\Storaging\Catalog\CatalogRemover;
use App\Services\Storaging\Catalog\Contracts\CatalogRemoverContract;
use App\Services\Storaging\Catalog\Contracts\DefaultCatalogFinderContract;
use App\Services\Storaging\Catalog\DefaultCatalogFinder;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $bindings = [
        CatalogRemoverContract::class => CatalogRemover::class,
        DefaultCatalogFinderContract::class => DefaultCatalogFinder::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->bindings);
    }
}
