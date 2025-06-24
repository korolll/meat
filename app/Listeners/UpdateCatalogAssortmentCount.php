<?php

namespace App\Listeners;

use App\Events\NeedCatalogAssortmentCountUpdate;
use App\Jobs\UpdateCatalogAssortmentCountJob;

class UpdateCatalogAssortmentCount
{
    /**
     * @param NeedCatalogAssortmentCountUpdate $event
     */
    public function handle(NeedCatalogAssortmentCountUpdate $event)
    {
        UpdateCatalogAssortmentCountJob::dispatch($event->catalogUuid);
    }
}
