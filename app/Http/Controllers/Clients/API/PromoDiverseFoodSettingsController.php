<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoDiverseFoodSettingsResource;
use App\Models\PromoDiverseFoodSettings;


class PromoDiverseFoodSettingsController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', PromoDiverseFoodSettings::class);

        $list = PromoDiverseFoodSettings::enabled()
            ->orderBy('discount_percent')
            ->get();

        return PromoDiverseFoodSettingsResource::collection($list);
    }

    /**
     * @param \App\Models\PromoDiverseFoodSettings $promoDiverseFoodSetting
     *
     * @return \App\Http\Resources\PromoDiverseFoodSettingsResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(PromoDiverseFoodSettings $promoDiverseFoodSetting)
    {
        $this->authorize('view', $promoDiverseFoodSetting);
        return PromoDiverseFoodSettingsResource::make($promoDiverseFoodSetting);
    }
}
