<?php

namespace App\Listeners;

use App\Contracts\Models\Catalog\FindPublicCatalogsContract;
use App\Events\PublicCatalogsReadyForExport1C;
use App\Jobs;

class ExportPublicCatalogsTo1C
{
    /**
     * @param PublicCatalogsReadyForExport1C $event
     */
    public function handle(PublicCatalogsReadyForExport1C $event)
    {
        if ($this->exportEnabled()) {
            Jobs\ExportCatalogsTo1C::dispatch(resolve(FindPublicCatalogsContract::class)->find());
        }
    }

    /**
     * @return bool
     */
    public function exportEnabled(): bool
    {
        return !empty(config('services.1c.catalog_exporter.uri'));
    }
}
