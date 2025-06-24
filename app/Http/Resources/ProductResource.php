<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class ProductResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name', 'assortment_verify_status_id');
            },
            'catalog' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'files' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
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
            'assortment_verify_status_id' => $product->assortment->assortment_verify_status_id,
            'catalog_uuid' => $product->catalog->uuid,
            'catalog_name' => $product->catalog->name,
            'quantum' => $product->quantum,
            'min_quantum_in_order' => $product->min_quantum_in_order,
            'min_delivery_time' => $product->min_delivery_time,
            'quantity' => $product->quantity,
            'price' => $product->price,
            'created_at' => $product->created_at,
            'price_recommended' => $product->price_recommended,
            'files' => FileShortInfoResource::collection($product->files),
            'delivery_weekdays' => $product->delivery_weekdays,
            'volume' => $product->volume,
        ];
    }
}
