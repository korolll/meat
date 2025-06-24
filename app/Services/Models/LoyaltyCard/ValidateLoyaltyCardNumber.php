<?php

namespace App\Services\Models\LoyaltyCard;

use App\Contracts\Models\LoyaltyCard\ValidateLoyaltyCardNumberContract;
use Illuminate\Support\Facades\Validator;

class ValidateLoyaltyCardNumber implements ValidateLoyaltyCardNumberContract
{
    /**
     * @param string $number
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(string $number): bool
    {
        Validator::validate(compact('number'), [
            'number' => 'digits_between:3,20',
        ]);

        return true;
    }
}
