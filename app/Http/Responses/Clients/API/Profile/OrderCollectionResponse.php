<?php

namespace App\Http\Responses\Clients\API\Profile;

use App\Http\Resources\Clients\API\Profile\OrderCollectionResource;
use App\Models\Order;
use App\Services\Framework\Http\EloquentCollectionResponse;

class OrderCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = OrderCollectionResource::class;

    /**
     * @var string
     */
    protected $model = Order::class;

    /**
     * @var array
     */
    protected $attributes = [
        'store_user_uuid',
        'client_uuid',
        'order_status_id',
        'order_delivery_type_id',
        'order_payment_type_id',
        'planned_delivery_datetime_from',
        'planned_delivery_datetime_to',
        'created_at',
        'updated_at',
        'number',
        'courier_phone',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'planned_delivery_datetime_from',
        'planned_delivery_datetime_to',
    ];
}
