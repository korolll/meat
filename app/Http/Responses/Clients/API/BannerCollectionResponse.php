<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\Clients\API\BannerResource;
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
        'id',
        'name',
        'description',
        'number',
        'enabled',
        'created_at',
        'reference_type',
        'reference_uuid'
    ];
}
