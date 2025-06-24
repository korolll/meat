<?php

namespace App\Services\Management\PriceList;

use App\Models\PriceList;

interface ProductManagerContract
{
    /**
     * @param PriceList $priceList
     * @return int
     */
    public function synchronize(PriceList $priceList);
}
