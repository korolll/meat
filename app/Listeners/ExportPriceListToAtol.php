<?php

namespace App\Listeners;

use App\Events\PriceListReadyForExportAtol;
use App\Jobs;

class ExportPriceListToAtol
{
    /**
     * @param PriceListReadyForExportAtol $event
     */
    public function handle(PriceListReadyForExportAtol $event)
    {
        if ($this->exportEnabled()) {
            Jobs\ExportPriceListToAtol::dispatch($event->priceList);
        }
    }

    /**
     * @return bool
     */
    public function exportEnabled(): bool
    {
        return !empty(config('services.atol.export.price_list.uri'));
    }
}
