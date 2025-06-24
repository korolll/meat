<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class AssortmentProductResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'user' => function (Relation $query) {
                return $query->select('uuid', 'organization_name');
            },
            'assortment' => function (Relation $query) {
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
            'user_uuid' => $product->user->uuid,
            'user_organization_name' => $product->user->organization_name,
            'user_supplier_rating' => $product->user_supplier_rating,
            'user_done_supplier_product_requests_count' => $product->user_done_supplier_product_requests_count,
            'assortment_uuid' => $product->assortment->uuid,
            'assortment_name' => $product->assortment->name,
            'quantum' => $product->quantum,
            'min_quantum_in_order' => $product->min_quantum_in_order,
            'min_delivery_time' => $product->min_delivery_time,
            'price' => $product->price,
            'delivery_weekdays' => $product->delivery_weekdays,
            'user_supplier_product_requests_today' => $product->user_supplier_product_requests_today,
        ];
    }
}
