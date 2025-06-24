<?php

namespace App\Listeners;

use App\Events\AssortmentUpdated;
use App\Jobs;
use App\Models\Assortment;
use App\Models\Product;

class ExportProductByAssortmentTo1C
{
    /**
     * @param Assortment $assortment
     * @param array $allowedUsers
     */
    protected function dispatchExport(Assortment $assortment, array $allowedUsers)
    {
        $assortment->products()->whereIn('products.user_uuid', $allowedUsers)->each(function (Product $product) {
            Jobs\ExportProductTo1C::dispatch($product);
        });
    }

    /**
     * @return bool
     */
    protected function exportEnabled(): bool
    {
        return !empty(config('services.1c.product_exporter.uri'));
    }

    /**
     * @param AssortmentUpdated $event
     */
    public function handle(AssortmentUpdated $event)
    {
        $allowedUsers = (array)config('services.1c.users_allowed_to_export');
        if ($this->exportEnabled() && $allowedUsers) {
            $this->dispatchExport($event->assortment, $allowedUsers);
        }
    }
}
