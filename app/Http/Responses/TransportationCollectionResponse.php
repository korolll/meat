<?php

namespace App\Http\Responses;

use App\Http\Resources\TransportationResourceCollection;
use App\Models\Transportation;
use App\Services\Framework\Http\EloquentCollectionResponse;

class TransportationCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = TransportationResourceCollection::class;

    /**
     * @var string
     */
    protected $model = Transportation::class;

    /**
     * @var array
     */
    protected $attributes = [
        'date',
        'car_uuid',
        'car_license_plate',
        'driver_uuid',
        'driver_full_name',
        'transportation_status_id',
        'started_at',
        'created_at',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'car_uuid' => 'car.uuid',
        'car_license_plate' => 'car.license_plate',
        'driver_uuid' => 'driver.uuid',
        'driver_full_name' => 'driver.full_name',
    ];
}
