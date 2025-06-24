<?php

namespace App\Services\Models\Catalog;

use App\Contracts\Models\Catalog\DetachAssortmentPropertyFromCatalogContract;
use App\Contracts\Models\Catalog\FindChildCatalogsContract;
use App\Models\AssortmentProperty;
use App\Models\Catalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DetachAssortmentPropertyFromCatalog implements DetachAssortmentPropertyFromCatalogContract
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
    public function detach(Catalog $catalog, AssortmentProperty $assortmentProperty): void
    {
        /** @var Collection&Catalog[] $catalogs */
        $catalogs = collect()->push($catalog)->merge(
            $this->findChildCatalogs->find($catalog)
        );

        DB::transaction(function () use ($catalogs, $assortmentProperty) {
            foreach ($catalogs as $catalog) {
                $this->detachFromAssortments($catalog, $assortmentProperty);
                $this->detachFromCatalog($catalog, $assortmentProperty);
            }
        });
    }

    /**
     * @param Catalog $catalog
     * @param AssortmentProperty $assortmentProperty
     */
    protected function detachFromAssortments(Catalog $catalog, AssortmentProperty $assortmentProperty): void
    {
        $assortmentUuids = $catalog->assortments()->getQuery()->select('uuid');

        DB::table('assortment_assortment_property')
            ->whereIn('assortment_uuid', $assortmentUuids)
            ->where('assortment_property_uuid', $assortmentProperty->uuid)
            ->delete();
    }

    /**
     * @param Catalog $catalog
     * @param AssortmentProperty $assortmentProperty
     */
    protected function detachFromCatalog(Catalog $catalog, AssortmentProperty $assortmentProperty): void
    {
        $catalog->assortmentProperties()->detach($assortmentProperty);
    }
}
