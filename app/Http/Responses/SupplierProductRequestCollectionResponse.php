<?php

namespace App\Http\Responses;

use App\Http\Resources\SupplierProductRequestResourceCollection;
use App\Models\ProductRequest;
use App\Services\Framework\Http\EloquentCollectionResponse;

class SupplierProductRequestCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = SupplierProductRequestResourceCollection::class;

    /**
     * @var string
     */
    protected $model = ProductRequest::class;

    /**
     * @var array
     */
    protected $attributes = [
        'customer_user_uuid',
        'customer_user_organization_name',
        'product_request_supplier_status_id',
        'created_at',
        'expected_delivery_date',
        'confirmed_date',
        'product_request_delivery_method_id',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'customer_user_uuid' => 'customerUser.uuid',
        'customer_user_organization_name' => 'customerUser.organization_name',
    ];
}