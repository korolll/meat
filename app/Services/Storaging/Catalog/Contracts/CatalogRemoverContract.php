<?php

namespace App\Services\Storaging\Catalog\Contracts;

use App\Exceptions\ClientExceptions\CatalogNotEmptyException;
use App\Models\Catalog;

interface CatalogRemoverContract
{
    /**
     * @param Catalog $catalog
     * @throws CatalogNotEmptyException
     */
    public function remove(Catalog $catalog);
}
