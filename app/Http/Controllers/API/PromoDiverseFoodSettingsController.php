<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoDiverseFoodSettingsStoreRequest;
use App\Http\Resources\PromoDiverseFoodSettingsResource;
use App\Http\Responses\PromoDiverseFoodSettingsCollectionResponse;
use App\Models\PromoDiverseFoodSettings;


class PromoDiverseFoodSettingsController extends Controller
{
    public function index()
    {
        $this->authorize('any', PromoDiverseFoodSettings::class);

        return PromoDiverseFoodSettingsCollectionResponse::create(
            PromoDiverseFoodSettings::query()
        );
    }

    public function store(PromoDiverseFoodSettingsStoreRequest $request)
    {
        $this->authorize('any', PromoDiverseFoodSettings::class);
        $promo = new PromoDiverseFoodSettings($request->validated());
        $promo->save();
        return PromoDiverseFoodSettingsResource::make($promo);
    }

    public function update(PromoDiverseFoodSettings $promoDiverseFoodSetting, PromoDiverseFoodSettingsStoreRequest $request)
    {
        $this->authorize('any', $promoDiverseFoodSetting);
        $promoDiverseFoodSetting->fill($request->validated());
        $promoDiverseFoodSetting->save();
        return PromoDiverseFoodSettingsResource::make($promoDiverseFoodSetting);
    }

    public function show(PromoDiverseFoodSettings $promoDiverseFoodSetting)
    {
        $this->authorize('any', $promoDiverseFoodSetting);
        return PromoDiverseFoodSettingsResource::make($promoDiverseFoodSetting);
    }

    public function destroy(PromoDiverseFoodSettings $promoDiverseFoodSetting)
    {
        $this->authorize('any', $promoDiverseFoodSetting);

        $promoDiverseFoodSetting->delete();
        return PromoDiverseFoodSettingsResource::make($promoDiverseFoodSetting);
    }

    public function toggleEnable(PromoDiverseFoodSettings $promoDiverseFoodSetting)
    {
        $this->authorize('any', $promoDiverseFoodSetting);
        $promoDiverseFoodSetting->is_enabled = !$promoDiverseFoodSetting->is_enabled;
        $promoDiverseFoodSetting->save();
        return PromoDiverseFoodSettingsResource::make($promoDiverseFoodSetting);
    }
}
