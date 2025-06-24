<?php

namespace App\Http\Responses;

use App\Http\Resources\TransportationPointResource;
use App\Models\TransportationPoint;
use App\Services\Framework\Http\EloquentCollectionResponse;

class TransportationPointCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = TransportationPointResource::class;

    /**
     * @var string
     */
    protected $model = TransportationPoint::class;

    /**
     * @var array
     */
    protected $attributes = [
        'product_request_uuid',
        'transportation_point_type_id',
        'address',
        'arrived_at',
    ];

    /**
     * @var bool
     */
    protected $paginated = false;
}
