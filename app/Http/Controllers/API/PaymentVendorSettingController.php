<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentVendorSettingStoreRequest;
use App\Http\Resources\PaymentVendorSettingResource;
use App\Models\PaymentVendorSetting;
use App\Services\Models\PaymentVendorSetting\PaymentVendorSettingRepositoryInterface;

class PaymentVendorSettingController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
    {
        $this->authorize('index', PaymentVendorSetting::class);

        return PaymentVendorSettingResource::collection(
            PaymentVendorSetting::all()
        );
    }

    public function show(PaymentVendorSetting $paymentVendorSetting): PaymentVendorSettingResource
    {
        $this->authorize('view', $paymentVendorSetting);

        return PaymentVendorSettingResource::make($paymentVendorSetting);
    }

    public function store(PaymentVendorSettingStoreRequest $request): PaymentVendorSettingResource
    {
        $this->authorize('create', PaymentVendorSetting::class);

        $validated = $request->validated();
        $stores = $validated['stores'];
        unset($validated['stores']);

        /** @var PaymentVendorSettingRepositoryInterface $repo */
        $repo = app(PaymentVendorSettingRepositoryInterface::class);
        $result = $repo->create($validated, $stores);

        return PaymentVendorSettingResource::make($result);
    }

    public function update(PaymentVendorSettingStoreRequest $request, PaymentVendorSetting $paymentVendorSetting): PaymentVendorSettingResource
    {
        $this->authorize('update', $paymentVendorSetting);

        $validated = $request->validated();
        $stores = $validated['stores'];
        unset($validated['stores']);

        /** @var PaymentVendorSettingRepositoryInterface $repo */
        $repo = app(PaymentVendorSettingRepositoryInterface::class);
        $result = $repo->update($paymentVendorSetting, $validated, $stores);

        return PaymentVendorSettingResource::make($result);
    }
}
