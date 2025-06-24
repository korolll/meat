<?php

namespace App\Services\Management\Stocktaking\Contracts;

use App\Models\Stocktaking;

interface ProductSynchronizerContract
{
    /**
     * @param Stocktaking $stocktaking
     * @param array $onlyThisCatalogUuids
     * @return int
     */
    public function synchronize(Stocktaking $stocktaking, array $onlyThisCatalogUuids = []);
}
