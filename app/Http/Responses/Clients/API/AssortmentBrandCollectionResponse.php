<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\Clients\API\AssortmentBrandResource;
use App\Models\AssortmentBrand;
use App\Services\Framework\Http\EloquentCollectionResponse;

class AssortmentBrandCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = AssortmentBrandResource::class;

    /**
     * @var string
     */
    protected $model = AssortmentBrand::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
    ];
}
