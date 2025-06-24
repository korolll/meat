<?php

namespace App\Contracts\Integrations\OneC;

use App\Models\PriceList;

interface PriceListExporterContract
{
    /**
     * @param PriceList $priceList
     * @return bool
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function export(PriceList $priceList): bool;
}
