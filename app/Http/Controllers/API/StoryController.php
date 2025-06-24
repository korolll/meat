<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoryStoreRequest;
use App\Http\Resources\StoryResource;
use App\Http\Responses\StoryCollectionResponse;
use App\Models\Story;
use Illuminate\Http\Response;

class StoryController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Story::class);
        return StoryCollectionResponse::create(Story::query());
    }

    /**
     * @param \App\Http\Requests\StoryStoreRequest $request
     *
     * @return \App\Http\Resources\StoryResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoryStoreRequest $request)
    {
        $this->authorize('create', Story::class);

        $validated = $request->validated();
        $story = new Story($validated);
        $story->save();

        return StoryResource::make($story);
    }

    /**
     * @param \App\Models\Story $story
     *
     * @return \App\Http\Resources\StoryResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Story $story)
    {
        $this->authorize('view', $story);
        return StoryResource::make($story);
    }

    /**
     * @param \App\Http\Requests\StoryStoreRequest $request
     * @param \App\Models\Story                    $story
     *
     * @return \App\Http\Resources\StoryResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(StoryStoreRequest $request, Story $story)
    {
        $this->authorize('update', $story);

        $validated = $request->validated();
        $story->fill($validated);
        $story->save();

        return StoryResource::make($story);
    }

    /**
     * @param \App\Models\Story $story
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Story $story)
    {
        $this->authorize('delete', $story);
        $story->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
