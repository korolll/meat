<?php

namespace App\Contracts\Models\LoyaltyCard;

use App\Models\LoyaltyCard;

interface CreateLoyaltyCardContract
{
    /**
     * @param string $loyaltyCardTypeUuid
     * @param string $number
     * @return LoyaltyCard
     */
    public function create(string $loyaltyCardTypeUuid, string $number): LoyaltyCard;
}
