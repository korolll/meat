<?php

namespace App\Providers\Management\Profiles\Promotions;

use App\Services\Management\Profiles\Promotions\InTheShopAssortmentFinder;
use App\Services\Management\Profiles\Promotions\InTheShopAssortmentFinderContract;
use App\Services\Management\Profiles\Promotions\InTheShopService;
use App\Services\Management\Profiles\Promotions\InTheShopServiceContract;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class InTheShopServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array|string[]
     */
    public array $singletons = [
        InTheShopServiceContract::class => InTheShopService::class,
        InTheShopAssortmentFinderContract::class => InTheShopAssortmentFinder::class,
    ];

    /**
     *
     */
    public function register()
    {
        $this->app->when(InTheShopAssortmentFinder::class)->needs('$config')->give(function () {
            return config('app.promotions.in_the_shop');
        });
        $this->app->when(InTheShopService::class)->needs('$discount')->give(function () {
            return config('app.promotions.in_the_shop.discount');
        });
    }

    /**
     * @return array|int[]|string[]
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
