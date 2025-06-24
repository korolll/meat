<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductPreRequest;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class ProductRequestProductResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name', 'is_storable');
            },
            'assortment.barcodes'
//            'preRequest' => function (Relation $query) {
//                return $query->select('id', 'status', 'quantity');
//            },
        ]);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function resource($product)
    {
        // pivot => product_product_request

        return [
            'product_uuid' => $product->uuid,
            'assortment_uuid' => $product->assortment->uuid,
            'assortment_name' => $product->assortment->name,
            'barcodes' => $product->assortment->barcodes->pluck('barcode'),
            'quantity' => $product->pivot->quantity,
            'quantity_actual' => $product->pivot->quantity_actual,
            'is_added_product' => $product->pivot->is_added_product,
            'price' => $product->pivot->price,
            'weight' => $product->pivot->weight,
            'volume' => $product->pivot->volume,
            'is_storable' => $product->assortment->is_storable,
            'product_pre_request_status' => $product->product_pre_requests_status ? ProductPreRequest::getStatusName($product->product_pre_requests_status) : null,
            'product_pre_request_quantity' => $product->product_pre_requests_quantity,
            'product_pre_requests_error' => $product->product_pre_requests_error,
        ];
    }
}
