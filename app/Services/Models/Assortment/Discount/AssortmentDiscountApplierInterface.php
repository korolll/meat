<?php

namespace App\Services\Models\Assortment\Discount;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\User;
use App\Services\Management\Client\Product\TargetEnum;

interface AssortmentDiscountApplierInterface
{
    /**
     * @param User $store
     * @param Client $client
     * @param array<string, Assortment> $assortmentsMapByUuid
     * @param bool $addCurrentPrice
     * @param bool $fakePromoDiverseFoodClientDiscount
     * @param TargetEnum $targetEnum
     *
     * @return void
     */
    public function apply(
        User $store,
        Client $client,
        array $assortmentsMapByUuid,
        bool $addCurrentPrice = false,
        bool $fakePromoDiverseFoodClientDiscount = false,
        TargetEnum $targetEnum = TargetEnum::ORDER
    ): void;
}
