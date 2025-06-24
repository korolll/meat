<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoFavoriteAssortmentSettingsStoreRequest;
use App\Http\Resources\PromoFavoriteAssortmentSettingsResource;
use App\Models\PromoFavoriteAssortmentSetting;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class PromoFavoriteAssortmentSettingController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('any', PromoFavoriteAssortmentSetting::class);

        $all = PromoFavoriteAssortmentSetting::all();
        return PromoFavoriteAssortmentSettingsResource::collection($all);
    }

    /**
     * @param \App\Http\Requests\PromoFavoriteAssortmentSettingsStoreRequest $request
     *
     * @return \App\Http\Resources\PromoFavoriteAssortmentSettingsResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(PromoFavoriteAssortmentSettingsStoreRequest $request)
    {
        $this->authorize('any', PromoFavoriteAssortmentSetting::class);
        $somethingExist = PromoFavoriteAssortmentSetting::query()->exists();

        // Allow creating only one row (until)
        if ($somethingExist) {
            throw new BadRequestHttpException();
        }

        $promo = new PromoFavoriteAssortmentSetting($request->validated());
        $promo->save();
        return PromoFavoriteAssortmentSettingsResource::make($promo);
    }

    /**
     * @param \App\Models\PromoFavoriteAssortmentSetting                     $setting
     * @param \App\Http\Requests\PromoFavoriteAssortmentSettingsStoreRequest $request
     *
     * @return \App\Http\Resources\PromoFavoriteAssortmentSettingsResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(PromoFavoriteAssortmentSetting $setting, PromoFavoriteAssortmentSettingsStoreRequest $request)
    {
        $this->authorize('any', $setting);
        $setting->fill($request->validated());
        $setting->save();
        return PromoFavoriteAssortmentSettingsResource::make($setting);
    }

    /**
     * @param \App\Models\PromoFavoriteAssortmentSetting $setting
     *
     * @return \App\Http\Resources\PromoFavoriteAssortmentSettingsResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(PromoFavoriteAssortmentSetting $setting)
    {
        $this->authorize('any', $setting);
        return PromoFavoriteAssortmentSettingsResource::make($setting);
    }

    /**
     * @param \App\Models\PromoFavoriteAssortmentSetting $setting
     *
     * @return \App\Http\Resources\PromoFavoriteAssortmentSettingsResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function toggleEnable(PromoFavoriteAssortmentSetting $setting)
    {
        $this->authorize('any', $setting);
        $setting->is_enabled = ! $setting->is_enabled;
        $setting->save();
        return PromoFavoriteAssortmentSettingsResource::make($setting);
    }
}
