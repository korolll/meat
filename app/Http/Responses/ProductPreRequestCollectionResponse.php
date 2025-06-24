<?php

namespace App\Http\Responses;

use App\Http\Resources\ProductPreRequestResource;
use App\Models\ProductPreRequest;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ProductPreRequestCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ProductPreRequestResource::class;

    /**
     * @var string
     */
    protected $model = ProductPreRequest::class;

    /**
     * @var array
     */
    protected $attributes = [
        'product_request_uuid',
        'product_uuid',
        'quantity',
        'status',
        'delivery_date',
        'confirmed_delivery_date',
    ];
}
