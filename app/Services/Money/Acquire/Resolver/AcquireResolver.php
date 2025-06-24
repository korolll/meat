<?php

namespace App\Services\Money\Acquire\Resolver;

use App\Models\ClientCreditCard;
use App\Models\PaymentVendor;
use App\Models\PaymentVendorSetting;
use App\Models\User;
use App\Services\Money\Acquire\AcquireInterface;
use Illuminate\Support\Arr;

class AcquireResolver implements AcquireResolverInterface
{
    private array $vendorToConfig;
    private array $vendorToClass;

    /**
     * @var array<string, AcquireInterface>
     */
    private array $cacheData = [];

    public function __construct(array $vendorToConfig, array $vendorToClass)
    {
        $this->vendorToConfig = $vendorToConfig;
        $this->vendorToClass = $vendorToClass;
    }

    public function resolveDefaultByVendor(string $vendorId = PaymentVendor::ID_SBERBANK): AcquireInterface
    {
        $config = $this->resolveBaseConfig($vendorId);
        return $this->resolveFinalObject($vendorId, $config);
    }

    public function resolveByClientCard(ClientCreditCard $card): AcquireInterface
    {
        $setting = $card->paymentVendorSetting;
        if ($setting) {
            return $this->resolveBySetting($setting);
        }

        return $this->resolveDefaultByVendor();
    }

    public function resolveBySetting(PaymentVendorSetting $setting): AcquireInterface
    {
        $vendorId = $setting->payment_vendor_id;
        $extraConfig = $setting->config;

        $config = $this->resolveConfig($vendorId, $extraConfig);
        return $this->resolveFinalObject($vendorId, $config);
    }

    public function getValidConfigFor(string $vendorId, array $config): ?array
    {
        /** @var AcquireInterface $class */
        $class = $this->resolveClass($vendorId);
        return $class::getValidConfig($config);
    }

    protected function resolveClass(string $vendorId): string
    {
        $class = Arr::get($this->vendorToClass, $vendorId);
        if (!$class || !class_exists($class) || !is_a($class, AcquireInterface::class, true)) {
            throw new \InvalidArgumentException('Invalid vendor id: ' . $vendorId);
        }

        return $class;
    }

    protected function resolveBaseConfig(string $vendorId): array
    {
        return Arr::get($this->vendorToConfig, $vendorId) ?: [];
    }

    protected function resolveConfig(string $vendorId, array $extraConfig = []): array
    {
        $baseConfig = $this->resolveBaseConfig($vendorId);
        return array_merge($baseConfig, $extraConfig);
    }

    protected function resolveFinalObject(string $vendorId, array $config): AcquireInterface
    {
        ksort($config);
        $cacheKey = $vendorId . sha1(join($config));

        if (!isset($this->cacheData[$cacheKey])) {
            $class = $this->resolveClass($vendorId);
            $this->cacheData[$cacheKey] = app($class, compact('config'));
        }

        return $this->cacheData[$cacheKey];
    }
}