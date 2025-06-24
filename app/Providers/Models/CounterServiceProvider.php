<?php

namespace App\Providers\Models;

use App\Contracts\Models\Counter\IncrementCounterContract;
use App\Contracts\Models\Counter\FindOrCreateCounterContract;
use App\Services\Models\Counter\IncrementCounter;
use App\Services\Models\Counter\FindOrCreateCounter;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class CounterServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        FindOrCreateCounterContract::class => FindOrCreateCounter::class,
        IncrementCounterContract::class => IncrementCounter::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
