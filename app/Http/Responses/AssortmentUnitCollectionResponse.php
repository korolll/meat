<?php

namespace App\Http\Responses;

use App\Http\Resources\AssortmentUnitResource;
use App\Models\AssortmentUnit;
use App\Services\Framework\Http\EloquentCollectionResponse;

class AssortmentUnitCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = AssortmentUnitResource::class;

    /**
     * @var string
     */
    protected $model = AssortmentUnit::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'short_name',
    ];

    /**
     * @var bool
     */
    protected $paginated = false;
}
