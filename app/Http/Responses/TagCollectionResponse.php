<?php

namespace App\Http\Responses;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Services\Framework\Http\EloquentCollectionResponse;

class TagCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = TagResource::class;

    /**
     * @var string
     */
    protected $model = Tag::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'fixed_in_filters',
        'created_at',
    ];
}
