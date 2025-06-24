<?php

namespace App\Http\Resources;

use App\Models\ProductPreRequest;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class ProductPreRequestResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {

    }

    /**
     * @param $productPreRequest ProductPreRequest
     * @return array
     */
    public function resource($productPreRequest)
    {
        return [
            'product_request_uuid' => $productPreRequest->product_request_uuid,
            'product_uuid' => $productPreRequest->product_uuid,
            'quantity' => $productPreRequest->quantity,
            'status' => ProductPreRequest::getStatusName($productPreRequest->status),
            'delivery_date' => $productPreRequest->delivery_date,
            'confirmed_delivery_date' => $productPreRequest->confirmed_delivery_date,
        ];
    }
}
