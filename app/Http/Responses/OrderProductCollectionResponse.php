<?php

namespace App\Http\Responses;

use App\Http\Resources\OrderProductResource;
use App\Models\OrderProduct;
use App\Services\Framework\Http\EloquentCollectionResponse;

class OrderProductCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = OrderProductResource::class;

    /**
     * @var string
     */
    protected $model = OrderProduct::class;

    /**
     * @var array
     */
    protected $attributes = [
        'order_uuid',
        'product_uuid',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [];
}
