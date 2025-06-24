<?php

namespace App\Providers\Models;

use App\Services\Models\PaymentVendorSetting\PaymentVendorSettingRepository;
use App\Services\Models\PaymentVendorSetting\PaymentVendorSettingRepositoryInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class PaymentVendorSettingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        PaymentVendorSettingRepositoryInterface::class => PaymentVendorSettingRepository::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
