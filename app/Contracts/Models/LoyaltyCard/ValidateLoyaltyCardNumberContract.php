<?php

namespace App\Contracts\Models\LoyaltyCard;

interface ValidateLoyaltyCardNumberContract
{
    /**
     * @param string $number
     * @return bool
     */
    public function validate(string $number): bool;
}
