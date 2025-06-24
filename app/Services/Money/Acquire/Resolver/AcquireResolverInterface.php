<?php

namespace App\Services\Money\Acquire\Resolver;

use App\Models\ClientCreditCard;
use App\Models\PaymentVendor;
use App\Models\PaymentVendorSetting;
use App\Models\User;
use App\Services\Money\Acquire\AcquireInterface;

interface AcquireResolverInterface
{
    public function resolveByClientCard(ClientCreditCard $card): AcquireInterface;

    public function resolveBySetting(PaymentVendorSetting $setting): AcquireInterface;

    public function resolveDefaultByVendor(string $vendorId = PaymentVendor::ID_SBERBANK): AcquireInterface;

    public function getValidConfigFor(string $vendorId, array $config): ?array;
}