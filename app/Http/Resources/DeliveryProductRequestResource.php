<?php

namespace App\Http\Resources;

use App\Models\ProductRequest;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class DeliveryProductRequestResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'customerUser' => function (Relation $query) {
                return $query->select('uuid', 'address');
            },
            'supplierUser' => function (Relation $query) {
                return $query->select('uuid', 'address');
            },
        ]);
    }

    /**
     * @param ProductRequest $productRequest
     * @return array
     */
    public function resource($productRequest)
    {
        return [
            'uuid' => $productRequest->uuid,
            'customer_user_uuid' => $productRequest->customerUser->uuid,
            'customer_user_address' => $productRequest->customerUser->address,
            'supplier_user_uuid' => $productRequest->supplierUser->uuid,
            'supplier_user_address' => $productRequest->supplierUser->address,
            'weight' => $productRequest->weight,
            'volume' => $productRequest->volume,
            'product_request_delivery_status_id' => $productRequest->product_request_delivery_status_id,
            'created_at' => $productRequest->created_at,
            'transportation_uuid' => $productRequest->transportation_uuid,
            'expected_delivery_date' => $productRequest->expected_delivery_date,
            'customer_comment' => $productRequest->customer_comment,
            'supplier_comment' => $productRequest->supplier_comment,
            'delivery_comment' => $productRequest->delivery_comment,
            'confirmed_date' => $productRequest->confirmed_date,
        ];
    }
}
