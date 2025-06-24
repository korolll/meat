<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\Framework\Http\Resources\Json\ResourceCollection;
use Illuminate\Database\Eloquent\Relations\Relation;

class PriceListProductResource extends ResourceCollection
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'catalog' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'assortment.tags',
            'assortment.barcodes'
        ]);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function resource($product)
    {
        // pivot => price_list_product

        return [
            'product_uuid' => $product->uuid,
            'assortment_uuid' => $product->assortment->uuid,
            'assortment_name' => $product->assortment->name,
            'barcodes' => $product->assortment->barcodes->pluck('barcode'),
            'catalog_uuid' => $product->catalog->uuid,
            'catalog_name' => $product->catalog->name,
            'price_old' => $product->pivot->price_old,
            'price_new' => $product->pivot->price_new,
            'price_recommended' => $product->price_recommended,
            'is_active' => $product->is_active,
            'tags' => TagNameResource::collection($product->assortment->tags)
        ];
    }
}
