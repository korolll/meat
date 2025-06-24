<?php

namespace App\Services\Integrations\Atol\Contracts;

use App\Models\PriceList;

interface AtolExportPriceListContract
{
    /**
     * @param PriceList $priceList
     */
    public function export(PriceList $priceList): void;
}
