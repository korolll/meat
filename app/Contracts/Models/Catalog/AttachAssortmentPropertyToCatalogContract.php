<?php

namespace App\Contracts\Models\Catalog;

use App\Models\AssortmentProperty;
use App\Models\Catalog;

interface AttachAssortmentPropertyToCatalogContract
{
    /**
     * @param Catalog $catalog
     * @param AssortmentProperty $assortmentProperty
     * @return void
     */
    public function attach(Catalog $catalog, AssortmentProperty $assortmentProperty): void;
}
