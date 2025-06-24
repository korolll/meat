<?php

namespace App\Services\Models\PaymentVendorSetting;

use App\Models\PaymentVendorSetting;

interface PaymentVendorSettingRepositoryInterface
{
    /**
     * @param array{
     *     payment_vendor_id: string,
     *     config: array
     * } $data
     * @param array<array{
     *     store_uuid: string,
     *     is_active: bool
     * }> $stores
     * @return PaymentVendorSetting
     */
    public function create(array $data, array $stores): PaymentVendorSetting;

    /**
     * @param PaymentVendorSetting $paymentVendorSetting
     * @param array{
     *     payment_vendor_id: string,
     *     config: array
     * } $data
     * @param array<array{
     *     store_uuid: string,
     *     is_active: bool
     * }> $stores
     * @return PaymentVendorSetting
     */
    public function update(PaymentVendorSetting $paymentVendorSetting, array $data, array $stores): PaymentVendorSetting;
}