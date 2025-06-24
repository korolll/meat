<?php

namespace App\Services\Management\Client\Order\System;

interface SystemOrderSettingStorageInterface
{
    public function getMinPrice(): ?float;

    public function getDeliveryThreshold(): ?float;
}