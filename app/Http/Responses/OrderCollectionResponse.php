<?php

namespace App\Http\Responses;

use App\Http\Resources\OrderCollectionResource;
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
        'client_phone',
        'order_status_id',
        'order_delivery_type_id',
        'order_payment_type_id',
        'client_email',
        'planned_delivery_datetime_from',
        'planned_delivery_datetime_to',
        'total_price',
        'paid_bonus',
        'bonus_to_charge',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'client_phone' => 'client.phone',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'client_email',
    ];
}
