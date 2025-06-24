<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannerStoreRequest;
use App\Http\Resources\BannerResource;
use App\Http\Responses\BannerCollectionResponse;
use App\Models\Banner;
use Illuminate\Http\Response;

class BannerController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Banner::class);
        return BannerCollectionResponse::create(Banner::query());
    }

    /**
     * @param \App\Http\Requests\BannerStoreRequest $request
     *
     * @return \App\Http\Resources\BannerResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(BannerStoreRequest $request)
    {
        $this->authorize('create', Banner::class);

        $validated = $request->validated();
        $banner = new Banner($validated);
        $banner->save();

        return BannerResource::make($banner);
    }

    /**
     * @param \App\Models\Banner $banner
     *
     * @return \App\Http\Resources\BannerResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Banner $banner)
    {
        $this->authorize('view', $banner);
        return BannerResource::make($banner);
    }

    /**
     * @param \App\Http\Requests\BannerStoreRequest $request
     * @param \App\Models\Banner                    $banner
     *
     * @return \App\Http\Resources\BannerResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(BannerStoreRequest $request, Banner $banner)
    {
        $this->authorize('update', $banner);

        $validated = $request->validated();
        $banner->fill($validated);
        $banner->save();

        return BannerResource::make($banner);
    }

    /**
     * @param \App\Models\Banner $banner
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Banner $banner)
    {
        $this->authorize('delete', $banner);
        $banner->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
