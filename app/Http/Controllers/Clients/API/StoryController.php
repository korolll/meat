<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Clients\API\StoryResource;
use App\Http\Responses\Clients\API\StoryCollectionResponse;
use App\Models\Story;

class StoryController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function index()
    {
        return StoryCollectionResponse::create(
            Story::showed()
        );
    }

    /**
     * @param \App\Models\Story $story
     *
     * @return \App\Http\Resources\Clients\API\StoryResource
     */
    public function show(Story $story)
    {
        return StoryResource::make($story);
    }
}
