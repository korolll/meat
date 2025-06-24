<?php

namespace App\Http\Responses;

use App\Http\Resources\StoryTabResource;
use App\Models\StoryTab;
use App\Services\Framework\Http\EloquentCollectionResponse;

class StoryTabCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = StoryTabResource::class;

    /**
     * @var string
     */
    protected $model = StoryTab::class;

    /**
     * @var array
     */
    protected $attributes = [
        'id',
        'story_id',
        'title',
        'text',
        'text_color',
        'duration',
        'button_title',
        'url',
        'logo_file_path',
        'created_at',
    ];
}
