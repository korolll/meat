<?php

namespace App\Http\Responses;

use App\Http\Resources\CarResource;
use App\Models\Car;
use App\Services\Framework\Http\EloquentCollectionResponse;

class CarCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = CarResource::class;

    /**
     * @var string
     */
    protected $model = Car::class;

    /**
     * @var array
     */
    protected $attributes = [
        'brand_name',
        'model_name',
        'license_plate',
        'call_sign',
        'max_weight',
        'is_active',
        'created_at',
    ];
}
