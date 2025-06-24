<?php

namespace App\Providers\Models;

use App\Contracts\Models\LoyaltyCard\CreateLoyaltyCardContract;
use App\Contracts\Models\LoyaltyCard\ValidateLoyaltyCardNumberContract;
use App\Services\Models\LoyaltyCard\CreateLoyaltyCard;
use App\Services\Models\LoyaltyCard\ValidateLoyaltyCardNumber;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class LoyaltyCardServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        CreateLoyaltyCardContract::class => CreateLoyaltyCard::class,
        ValidateLoyaltyCardNumberContract::class => ValidateLoyaltyCardNumber::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
