<?php

namespace App\Http\Resources;

use App\Models\ProductRequests\SupplierProductRequest;
use App\Services\Framework\Http\Resources\Json\ResourceCollection;
use Illuminate\Database\Eloquent\Relations\Relation;

class SupplierProductRequestResourceCollection extends ResourceCollection
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'customerUser' => function (Relation $query) {
                return $query->select('uuid', 'organization_name');
            },
            'relatedCustomerProductRequests' => function (Relation $query) {
                return $query->with('customerUser');
            },
        ]);
    }

    /**
     * @param SupplierProductRequest $productRequest
     * @return array
     */
    public function resource($productRequest)
    {
        return [
            'uuid' => $productRequest->uuid,
            'customer_user_uuid' => $productRequest->customerUser->uuid,
            'customer_user_organization_name' => $productRequest->customerUser->organization_name,
            'product_request_supplier_status_id' => $productRequest->product_request_supplier_status_id,
            'created_at' => $productRequest->created_at,
            'expected_delivery_date' => $productRequest->expected_delivery_date,
            'product_request_delivery_method_id' => $productRequest->product_request_delivery_method_id,
            'related_customer_product_requests' => RelatedCustomerProductRequestResource::collection
            ($productRequest->relatedCustomerProductRequests),
            'customer_comment' => $productRequest->customer_comment,
            'supplier_comment' => $productRequest->supplier_comment,
            'delivery_comment' => $productRequest->delivery_comment,
            'confirmed_date' => $productRequest->confirmed_date
        ];
    }
}
