<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\Clients\API\StoryResource;
use App\Models\Story;
use App\Services\Framework\Http\EloquentCollectionResponse;

class StoryCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = StoryResource::class;

    /**
     * @var string
     */
    protected $model = Story::class;

    /**
     * @var array
     */
    protected $attributes = [
        'id',
        'title',
        'text',
        'created_at',
    ];
}
