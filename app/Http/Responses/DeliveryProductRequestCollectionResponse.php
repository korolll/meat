<?php

namespace App\Http\Responses;

use App\Http\Resources\DeliveryProductRequestResource;
use App\Models\ProductRequest;
use App\Services\Framework\Http\EloquentCollectionResponse;

class DeliveryProductRequestCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = DeliveryProductRequestResource::class;

    /**
     * @var string
     */
    protected $model = ProductRequest::class;

    /**
     * @var array
     */
    protected $attributes = [
        'customer_user_address',
        'customer_user_uuid',
        'supplier_user_address',
        'supplier_user_uuid',
        'weight',
        'volume',
        'product_request_delivery_status_id',
        'created_at',
        'transportation_uuid',
        'confirmed_date',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'customer_user_address' => 'customerUser.address',
        'customer_user_uuid' => 'customerUser.uuid',
        'supplier_user_address' => 'supplierUser.address',
        'supplier_user_uuid' => 'supplierUser.uuid',
    ];
}
