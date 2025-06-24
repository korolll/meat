<?php

namespace App\Providers\Models;

use App\Services\Models\Client\ClientCart;
use App\Services\Models\Client\ClientCartInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $bindings = [
        ClientCartInterface::class => ClientCart::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->bindings);
    }
}
