<?php

namespace App\Listeners;

use App\Events\ProductReadyForExport;
use App\Jobs;

class ExportProductTo1C
{
    /**
     * @param ProductReadyForExport $event
     */
    public function handle(ProductReadyForExport $event)
    {
        if ($this->exportEnabled() && $event->product->need_export_to_one_c) {
            Jobs\ExportProductTo1C::dispatch($event->product);
        }
    }

    /**
     * @return bool
     */
    public function exportEnabled(): bool
    {
        return !empty(config('services.1c.product_exporter.uri'));
    }
}
