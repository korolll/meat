<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\SocialResource;
use App\Models\Social;
use App\Services\Framework\Http\EloquentCollectionResponse;

class SocialCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = SocialResource::class;

    /**
     * @var string
     */
    protected $model = Social::class;

    /**
     * @var array
     */
    protected $attributes = [
        'title',
        'sort_number',
        'created_at',
    ];
}
