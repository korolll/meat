<?php

namespace App\Http\Resources;

use App\Models\ProductRequests\SupplierProductRequest;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class SupplierProductRequestResource extends JsonResource
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
            'transportation' => function (Relation $query) {
                return $query->select('uuid', 'car_uuid', 'driver_uuid');
            },
            'transportation.car' => function (Relation $query) {
                return $query->select('uuid', 'brand_name', 'model_name');
            },
            'transportation.driver' => function (Relation $query) {
                return $query->select('uuid', 'full_name');
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
        $transportation = optional($productRequest->transportation);
        $car = optional($transportation->car);
        $driver = optional($transportation->driver);

        return [
            'uuid' => $productRequest->uuid,
            'expected_delivery_date' => $productRequest->expected_delivery_date,
            'customer_user_uuid' => $productRequest->customerUser->uuid,
            'customer_user_organization_name' => $productRequest->customerUser->organization_name,
            'product_request_customer_status_id' => $productRequest->product_request_customer_status_id,
            'product_request_supplier_status_id' => $productRequest->product_request_supplier_status_id,
            'product_request_delivery_status_id' => $productRequest->product_request_delivery_status_id,
            'car_uuid' => $car->uuid,
            'car_brand_name' => $car->brand_name,
            'car_model_name' => $car->model_name,
            'driver_uuid' => $driver->uuid,
            'driver_full_name' => $driver->full_name,
            'product_request_delivery_method_id' => $productRequest->product_request_delivery_method_id,
            'related_customer_product_requests' => RelatedCustomerProductRequestResource::collection($productRequest->relatedCustomerProductRequests),
            'created_at' => $productRequest->created_at,
            'customer_comment' => $productRequest->customer_comment,
            'supplier_comment' => $productRequest->supplier_comment,
            'delivery_comment' => $productRequest->delivery_comment,
            'confirmed_date' => $productRequest->confirmed_date
        ];
    }
}
