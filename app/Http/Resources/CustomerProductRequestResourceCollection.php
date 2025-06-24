<?php

namespace App\Http\Resources;

use App\Models\ProductRequest;
use App\Models\ProductRequests\CustomerProductRequest;
use App\Services\Framework\Http\Resources\Json\ResourceCollection;
use Illuminate\Database\Eloquent\Relations\Relation;

class CustomerProductRequestResourceCollection extends ResourceCollection
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'supplierUser' => function (Relation $query) {
                return $query->select('uuid', 'organization_name');
            },
            'relatedSupplierProductRequests' => function (Relation $query) {
                return $query->select('uuid');
            },
        ]);
    }

    /**
     * @param CustomerProductRequest $productRequest
     * @return array
     */
    public function resource($productRequest)
    {
        return [
            'uuid' => $productRequest->uuid,
            'supplier_user_uuid' => $productRequest->supplierUser->uuid,
            'supplier_user_organization_name' => $productRequest->supplierUser->organization_name,
            'product_request_customer_status_id' => $productRequest->product_request_customer_status_id,
            'created_at' => $productRequest->created_at,
            'expected_delivery_date' => $productRequest->expected_delivery_date,
            'product_request_delivery_method_id' => $productRequest->product_request_delivery_method_id,
            'related_supplier_product_requests' => $productRequest->relatedSupplierProductRequests->map->only('uuid'),
            'customer_comment' => $productRequest->customer_comment,
            'supplier_comment' => $productRequest->supplier_comment,
            'delivery_comment' => $productRequest->delivery_comment,
            'confirmed_date' => $productRequest->confirmed_date,
        ];
    }
}
