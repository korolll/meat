<?php

namespace App\Providers\Models;

use App\Contracts\Models\Catalog\AttachAssortmentPropertyToCatalogContract;
use App\Contracts\Models\Catalog\DetachAssortmentPropertyFromCatalogContract;
use App\Contracts\Models\Catalog\FindChildCatalogsContract;
use App\Contracts\Models\Catalog\FindPublicCatalogsContract;
use App\Services\Models\Catalog\AttachAssortmentPropertyToCatalog;
use App\Services\Models\Catalog\DetachAssortmentPropertyFromCatalog;
use App\Services\Models\Catalog\FindChildCatalogs;
use App\Services\Models\Catalog\FindPublicCatalogs;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        AttachAssortmentPropertyToCatalogContract::class => AttachAssortmentPropertyToCatalog::class,
        DetachAssortmentPropertyFromCatalogContract::class => DetachAssortmentPropertyFromCatalog::class,
        FindChildCatalogsContract::class => FindChildCatalogs::class,
        FindPublicCatalogsContract::class => FindPublicCatalogs::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
