<?php

namespace App\Providers\Integrations;

use App\Services\Integrations\Frontol\LoyaltySystem;
use App\Services\Integrations\Frontol\LoyaltySystemInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class FrontolServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LoyaltySystemInterface::class, LoyaltySystem::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            LoyaltySystemInterface::class,
        ];
    }
}
