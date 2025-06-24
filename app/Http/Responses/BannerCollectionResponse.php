<?php

namespace App\Http\Responses;

use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Services\Framework\Http\EloquentCollectionResponse;

class BannerCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = BannerResource::class;

    /**
     * @var string
     */
    protected $model = Banner::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'description',
        'enabled',
        'number',
        'created_at',
        'reference_type',
        'reference_uuid'
    ];
}
