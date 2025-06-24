<?php

namespace App\Http\Responses;

use App\Http\Resources\AssortmentBrandResource;
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
