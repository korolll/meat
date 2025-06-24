<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\Framework\Http\Resources\Json\ResourceCollection;
use Illuminate\Database\Eloquent\Relations\Relation;

class ProductResourceCollection extends ResourceCollection
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name', 'assortment_verify_status_id', 'catalog_uuid');
            },
            'assortment.tags',
            'assortment.barcodes',
            'assortment.catalog',
            'catalog' => function (Relation $query) {
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
        return [
            'uuid' => $product->uuid,
            'assortment_uuid' => $product->assortment->uuid,
            'assortment_name' => $product->assortment->name,
            'assortment_catalog_uuid' => $product->assortment->catalog->uuid,
            'assortment_catalog_name' => $product->assortment->catalog->name,
            'barcodes' => $product->assortment->barcodes->pluck('barcode'),
            'assortment_verify_status_id' => $product->assortment->assortment_verify_status_id,
            'assortment_tags' => $product->assortment->tags->pluck('name'),
            'catalog_uuid' => $product->catalog->uuid,
            'catalog_name' => $product->catalog->name,
            'delivery_weekdays' => $product->delivery_weekdays,
            'created_at' => $product->created_at,
        ];
    }
}
