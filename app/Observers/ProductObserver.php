<?php

namespace App\Observers;

use App\Events\NeedCatalogProductCountUpdate;
use App\Events\PriceListReadyForExportAtol;
use App\Events\ProductReadyForExport;
use App\Models\PriceList;
use App\Models\Product;

class ProductObserver
{
    /**
     * @param Product $product
     */
    public function saved(Product $product)
    {
        ProductReadyForExport::dispatch($product);

        if ($product->isDirty('catalog_uuid') || $product->isDirty('assortment_uuid')) {
            $this->updateCatalogProductCount($product);
        }

        if ($product->isDirty('is_active')) {
            $product->priceLists()->current()->each(function (PriceList $priceList) {
                PriceListReadyForExportAtol::dispatch($priceList);
            });
        }
    }

    /**
     * @param Product $product
     */
    public function deleted(Product $product)
    {
        NeedCatalogProductCountUpdate::dispatch($product);
    }

    /**
     * @param Product $product
     */
    public function restored(Product $product)
    {
        NeedCatalogProductCountUpdate::dispatch($product);
    }

    /**
     * @param Product $product
     */
    private function updateCatalogProductCount(Product $product)
    {
        if (($catalogUuid = $product->getOriginal('catalog_uuid')) !== null) {
            NeedCatalogProductCountUpdate::dispatch($catalogUuid);
        }

        if (($catalogUuid = $product->catalog_uuid) !== null) {
            NeedCatalogProductCountUpdate::dispatch($catalogUuid);
        }
    }
}
