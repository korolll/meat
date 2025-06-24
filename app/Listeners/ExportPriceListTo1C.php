<?php

namespace App\Listeners;

use App\Events\PriceListReadyForExport1C;
use App\Jobs;
use App\Models\User;

class ExportPriceListTo1C
{
    /**
     * @param PriceListReadyForExport1C $event
     */
    public function handle(PriceListReadyForExport1C $event)
    {
        if ($this->exportEnabled($event->priceList->user)) {
            Jobs\ExportPriceListTo1C::dispatch($event->priceList);
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function exportEnabled(User $user): bool
    {
        $allowedUsers = (array) config('services.1c.users_allowed_to_export');
        return !empty(config('services.1c.price_list_exporter.uri')) && in_array($user->uuid, $allowedUsers);
    }
}
