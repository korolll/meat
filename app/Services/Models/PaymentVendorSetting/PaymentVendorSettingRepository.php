<?php

namespace App\Services\Models\PaymentVendorSetting;

use App\Exceptions\ClientExceptions\PaymentVendorSettingInvalidConfigException;
use App\Models\PaymentVendorSetting;
use App\Services\Money\Acquire\Resolver\AcquireResolverInterface;

class PaymentVendorSettingRepository implements PaymentVendorSettingRepositoryInterface
{
    public function __construct(
        protected readonly AcquireResolverInterface $resolver
    )
    {

    }

    /**
     * @inerhitDoc
     */
    public function create(array $data, array $stores): PaymentVendorSetting
    {
        return $this->doSave(new PaymentVendorSetting, $data, $stores);
    }

    /**
     * @inerhitDoc
     */
    public function update(PaymentVendorSetting $paymentVendorSetting, array $data, array $stores): PaymentVendorSetting
    {
        return $this->doSave($paymentVendorSetting, $data, $stores);
    }

    protected function doSave(PaymentVendorSetting $setting, array $data, array $stores): PaymentVendorSetting
    {
        $validConfig = $this->resolver->getValidConfigFor($data['payment_vendor_id'], $data['config']);
        if (!$validConfig) {
            throw new PaymentVendorSettingInvalidConfigException();
        }

        $setting->payment_vendor_id = $data['payment_vendor_id'];
        $setting->config = $validConfig;

        $toSync = [];
        foreach ($stores as $store) {
            $id = $store['store_uuid'];
            $toSync[$id] = ['is_active' => $store['is_active']];
        }

        $setting->save();
        $setting->users()->sync($toSync);
        return $setting;
    }
}