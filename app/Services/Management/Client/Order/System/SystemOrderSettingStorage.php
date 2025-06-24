<?php

namespace App\Services\Management\Client\Order\System;

use App\Models\SystemOrderSetting;
use Illuminate\Support\Arr;

class SystemOrderSettingStorage implements SystemOrderSettingStorageInterface
{
    /**
     * @var array<string, ?string>
     */
    protected array $settingsMap;

    public function __construct()
    {
        $this->settingsMap = [];
        /** @var SystemOrderSetting $setting */
        foreach (SystemOrderSetting::all() as $setting) {
            $this->settingsMap[$setting->id] = $setting->value;
        }
    }

    public function getMinPrice(): ?float
    {
        return $this->resolveFloat(SystemOrderSetting::ID_MIN_PRICE);
    }

    public function getDeliveryThreshold(): ?float
    {
        $result = $this->resolveFloat(SystemOrderSetting::ID_DELIVERY_THRESHOLD);
        if ($result === null) {
            // fallback
            $result = (float)config('app.order.price.delivery.free_threshold', 1000);
        }

        return $result;
    }

    protected function resolveFloat(string $key): ?float
    {
        $value = Arr::get($this->settingsMap, $key);
        if (! is_numeric($value)) {
            return null;
        }

        return (float)$value;
    }
}