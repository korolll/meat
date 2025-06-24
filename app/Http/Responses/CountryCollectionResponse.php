<?php

namespace App\Http\Responses;

use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Services\Framework\Http\EloquentCollectionResponse;

class CountryCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = CountryResource::class;

    /**
     * @var string
     */
    protected $model = Country::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
    ];

    /**
     * @var bool
     */
    protected $paginated = false;
}
