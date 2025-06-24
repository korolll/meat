<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class StocktakingProductResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name', 'catalog_uuid');
            },
            'assortment.tags',
            'assortment.barcodes',
            'assortment.catalog' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
        ]);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function resource($product)
    {
        // pivot => product_stocktaking

        return [
            'product_uuid' => $product->uuid,
            'assortment_uuid' => $product->assortment->uuid,
            'assortment_name' => $product->assortment->name,
            'assortment_catalog_uuid' => $product->assortment->catalog->uuid,
            'assortment_catalog_name' => $product->assortment->catalog->name,
            'barcodes' => $product->assortment->barcodes->pluck('barcode'),
            'write_off_reason_id' => $product->pivot->write_off_reason_id,
            'quantity_old' => $product->pivot->quantity_old,
            'quantity_new' => $product->pivot->quantity_new,
            'comment' => $product->pivot->comment,
            'tags' => TagNameResource::collection($product->assortment->tags)
        ];
    }
}
