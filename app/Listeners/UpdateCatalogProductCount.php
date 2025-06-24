<?php

namespace App\Listeners;

use App\Events\NeedCatalogProductCountUpdate;
use App\Jobs\UpdateCatalogProductCountJob;

class UpdateCatalogProductCount
{
    /**
     * @param NeedCatalogProductCountUpdate $event
     */
    public function handle(NeedCatalogProductCountUpdate $event)
    {
        UpdateCatalogProductCountJob::dispatch($event->catalogUuid);
    }
}
