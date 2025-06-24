<?php

namespace App\Contracts\Models\Catalog;

use App\Models\AssortmentProperty;
use App\Models\Catalog;

interface DetachAssortmentPropertyFromCatalogContract
{
    /**
     * @param Catalog $catalog
     * @param AssortmentProperty $assortmentProperty
     * @return void
     */
    public function detach(Catalog $catalog, AssortmentProperty $assortmentProperty): void;
}
