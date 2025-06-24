<?php

namespace App\Http\Responses;

use App\Http\Resources\DriverResource;
use App\Models\Driver;
use App\Services\Framework\Http\EloquentCollectionResponse;

class DriverCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = DriverResource::class;

    /**
     * @var string
     */
    protected $model = Driver::class;

    /**
     * @var array
     */
    protected $attributes = [
        'full_name',
        'email',
        'hired_on',
        'fired_on',
        'comment',
        'license_number',
        'created_at',
    ];
}
