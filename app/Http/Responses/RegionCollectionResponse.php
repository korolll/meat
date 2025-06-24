<?php

namespace App\Http\Responses;

use App\Http\Resources\RegionResource;
use App\Models\Region;
use App\Services\Framework\Http\EloquentCollectionResponse;

class RegionCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = RegionResource::class;

    /**
     * @var string
     */
    protected $model = Region::class;

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
