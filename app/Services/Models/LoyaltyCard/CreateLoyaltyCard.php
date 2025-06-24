<?php

namespace App\Services\Models\LoyaltyCard;

use App\Contracts\Models\LoyaltyCard\CreateLoyaltyCardContract;
use App\Contracts\Models\LoyaltyCard\ValidateLoyaltyCardNumberContract;
use App\Models\LoyaltyCard;

class CreateLoyaltyCard implements CreateLoyaltyCardContract
{
    /**
     * @var ValidateLoyaltyCardNumberContract
     */
    protected $validator;

    /**
     * @param ValidateLoyaltyCardNumberContract $validator
     */
    public function __construct(ValidateLoyaltyCardNumberContract $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string $loyaltyCardTypeUuid
     * @param string $number
     * @return LoyaltyCard
     */
    public function create(string $loyaltyCardTypeUuid, string $number): LoyaltyCard
    {
        $this->validator->validate($number);

        return LoyaltyCard::firstOrCreate([
            'loyalty_card_type_uuid' => $loyaltyCardTypeUuid,
            'number' => $number,
        ]);
    }
}
