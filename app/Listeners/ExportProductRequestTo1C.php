<?php

namespace App\Listeners;

use App\Jobs;

class ExportProductRequestTo1C
{
    /**
     * @param \App\Events\ProductRequestCreated|\App\Events\ProductRequestStatusChanged $event
     */
    public function handle($event): void
    {
        if ($event->isExportTo1C && $this->exportEnabled()) {
            Jobs\ExportProductRequestTo1C::dispatch($event->productRequest);
        }
    }

    /**
     * @return bool
     */
    public function exportEnabled(): bool
    {
        return config('services.1c.product_request_exporter.uri') !== null;
    }
}
