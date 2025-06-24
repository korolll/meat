<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemOrderSettingUpdateRequest;
use App\Http\Resources\SystemOrderSettingResource;
use App\Http\Responses\SystemOrderSettingCollectionResponse;
use App\Models\SystemOrderSetting;
use Illuminate\Auth\Access\AuthorizationException;

class SystemOrderSettingController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', SystemOrderSetting::class);

        return SystemOrderSettingCollectionResponse::create(
            SystemOrderSetting::query()
        );
    }

    /**
     * @param SystemOrderSetting $orderSetting
     * @return SystemOrderSettingResource
     * @throws AuthorizationException
     */
    public function show(SystemOrderSetting $orderSetting)
    {
        $this->authorize('view', $orderSetting);

        return SystemOrderSettingResource::make($orderSetting);
    }

    /**
     * @param SystemOrderSettingUpdateRequest $request
     * @param SystemOrderSetting $orderSetting
     * @return SystemOrderSettingResource
     * @throws AuthorizationException
     * @throws \Throwable
     */
    public function update(SystemOrderSettingUpdateRequest $request, SystemOrderSetting $orderSetting)
    {
        $this->authorize('update', $orderSetting);

        $orderSetting->fill($request->validated());
        $orderSetting->saveOrFail();

        return SystemOrderSettingResource::make($orderSetting);
    }
}
