<?php

namespace App\Services\Models\Catalog;

use App\Contracts\Models\Catalog\AttachAssortmentPropertyToCatalogContract;
use App\Contracts\Models\Catalog\FindChildCatalogsContract;
use App\Models\AssortmentProperty;
use App\Models\Catalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttachAssortmentPropertyToCatalog implements AttachAssortmentPropertyToCatalogContract
{
    /**
     * @var FindChildCatalogsContract
     */
    protected $findChildCatalogs;

    /**
     * @param FindChildCatalogsContract $findChildCatalogs
     */
    public function __construct(FindChildCatalogsContract $findChildCatalogs)
    {
        $this->findChildCatalogs = $findChildCatalogs;
    }

    /**
     * @param Catalog $catalog
     * @param AssortmentProperty $assortmentProperty
     * @return void
     */
    public function attach(Catalog $catalog, AssortmentProperty $assortmentProperty): void
    {
        /** @var Collection&Catalog[] $catalogs */
        $catalogs = collect()->push($catalog)->merge(
            $this->findChildCatalogs->find($catalog)
        );

        DB::transaction(function () use ($catalogs, $assortmentProperty) {
            foreach ($catalogs as $catalog) {
                $catalog->assortmentProperties()->attach($assortmentProperty);
            }
        });
    }
}
