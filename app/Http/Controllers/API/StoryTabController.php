<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoryTabStoreRequest;
use App\Http\Resources\StoryTabResource;
use App\Http\Responses\StoryTabCollectionResponse;
use App\Models\StoryTab;
use Illuminate\Http\Response;

class StoryTabController extends Controller
{
    public function index()
    {
        $this->authorize('index', StoryTab::class);
        return StoryTabCollectionResponse::create(StoryTab::query());
    }

    public function store(StoryTabStoreRequest $request)
    {
        $this->authorize('create', StoryTab::class);

        $validated = $request->validated();
        $StoryTab = new StoryTab($validated);
        $StoryTab->save();

        return StoryTabResource::make($StoryTab);
    }

    public function show(StoryTab $StoryTab)
    {
        $this->authorize('view', $StoryTab);
        return StoryTabResource::make($StoryTab);
    }

    public function update(StoryTabStoreRequest $request, StoryTab $storyTab)
    {
        $this->authorize('update', $storyTab);

        $validated = $request->validated();
        $storyTab->fill($validated);
        $storyTab->save();

        return StoryTabResource::make($storyTab);
    }

    public function destroy(StoryTab $storyTab)
    {
        $this->authorize('delete', $storyTab);
        $storyTab->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
