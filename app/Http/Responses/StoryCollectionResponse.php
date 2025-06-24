<?php

namespace App\Http\Responses;

use App\Http\Resources\StoryResource;
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
        'name',
        'show_from',
        'show_to',
        'created_at'
    ];
}
