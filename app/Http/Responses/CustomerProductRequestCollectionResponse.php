<?php

namespace App\Http\Responses;

use App\Http\Resources\CustomerProductRequestResourceCollection;
use App\Models\ProductRequest;
use App\Services\Framework\Http\EloquentCollectionResponse;

class CustomerProductRequestCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = CustomerProductRequestResourceCollection::class;

    /**
     * @var string
     */
    protected $model = ProductRequest::class;

    /**
     * @var array
     */
    protected $attributes = [
        'supplier_user_organization_name',
        'supplier_user_uuid',
        'product_request_customer_status_id',
        'created_at',
        'expected_delivery_date',
        'product_request_delivery_method_id',
        'confirmed_date',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'supplier_user_organization_name' => 'supplierUser.organization_name',
        'supplier_user_uuid' => 'supplierUser.uuid',
    ];
}
