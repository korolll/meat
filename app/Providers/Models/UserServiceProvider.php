<?php

namespace App\Providers\Models;

use App\Services\Models\User\ProductsInCatalogCacher;
use App\Services\Models\User\ProductsInCatalogCacherInterface;
use App\Services\Models\User\UserUpdater;
use App\Services\Models\User\UserUpdaterInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        UserUpdaterInterface::class => UserUpdater::class,
        ProductsInCatalogCacherInterface::class => ProductsInCatalogCacher::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
