<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Clients\API\BannerResource;
use App\Http\Responses\Clients\API\BannerCollectionResponse;
use App\Models\Banner;

class BannerController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function index()
    {
        return BannerCollectionResponse::create(
            Banner::query()
                ->where('enabled', '=', true)
                ->whereNull('deleted_at')
        );
    }

    /**
     * @param \App\Models\Banner $banner
     *
     * @return \App\Http\Resources\Clients\API\BannerResource
     */
    public function show(Banner $banner)
    {
        return BannerResource::make($banner);
    }
}
