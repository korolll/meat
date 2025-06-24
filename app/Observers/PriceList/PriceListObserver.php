<?php

namespace App\Observers\PriceList;

use App\Events\PriceListReadyForExport1C;
use App\Events\PriceListReadyForExportAtol;
use App\Models\PriceList;
use App\Models\PriceListStatus;


class PriceListObserver
{
    /**
     * @param PriceList $priceList
     */
    public function updated(PriceList $priceList)
    {
        // если прайс лист стал актуальным запустим событие
        $wasFuture = $priceList->getOriginal('price_list_status_id') === PriceListStatus::FUTURE;
        if ($wasFuture && $priceList->is_current) {
            PriceListReadyForExport1C::dispatch($priceList);
            PriceListReadyForExportAtol::dispatch($priceList);
        }
    }
}
