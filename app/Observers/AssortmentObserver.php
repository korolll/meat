<?php

namespace App\Observers;

use App\Events\AssortmentCreated;
use App\Events\AssortmentUpdated;
use App\Events\NeedCatalogAssortmentCountUpdate;
use App\Events\NeedCatalogProductCountUpdate;
use App\Models\Assortment;
use App\Models\Product;

class AssortmentObserver
{

    /**
     * Handle the assortment "created" event.
     *
     * @param  \App\Models\Assortment $assortment
     * @return void
     */
    public function created(Assortment $assortment)
    {
        AssortmentCreated::dispatch($assortment);
    }

    /**
     * @param Assortment $assortment
     */
    public function updated(Assortment $assortment)
    {
        AssortmentUpdated::dispatch($assortment);
    }

    /**
     * @param Assortment $assortment
     */
    public function saved(Assortment $assortment)
    {
        if ($assortment->is_approved &&
            ($assortment->isDirty('catalog_uuid') || $assortment->isDirty('assortment_verify_status_id'))) {
            $this->updateCatalogAssortmentCount($assortment);
        }

        if ($assortment->isDirty('assortment_verify_status_id') && $assortment->is_declined) {
            $assortment->products()->each(function (Product $product) {
                NeedCatalogProductCountUpdate::dispatch($product->catalog_uuid);
            });
        }
    }

    /**
     * @param Assortment $assortment
     */
    public function deleted(Assortment $assortment)
    {
        if ($assortment->is_approved) {
            NeedCatalogAssortmentCountUpdate::dispatch($assortment);
        }
    }

    /**
     * @param Assortment $assortment
     */
    public function restored(Assortment $assortment)
    {
        if ($assortment->is_approved) {
            NeedCatalogAssortmentCountUpdate::dispatch($assortment);
        }
    }

    /**
     * @param Assortment $assortment
     */
    private function updateCatalogAssortmentCount(Assortment $assortment)
    {
        if (($catalogUuid = $assortment->getOriginal('catalog_uuid')) !== null) {
            NeedCatalogAssortmentCountUpdate::dispatch($catalogUuid);
        }

        if (($catalogUuid = $assortment->catalog_uuid) !== null) {
            NeedCatalogAssortmentCountUpdate::dispatch($catalogUuid);
        }
    }
}
