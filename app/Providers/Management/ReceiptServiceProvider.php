<?php

namespace App\Providers\Management;

use App\Services\Management\Receipt\Contracts\ReceiptFactoryContract;
use App\Services\Management\Receipt\ReceiptFactory;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ReceiptServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $bindings = [
        ReceiptFactoryContract::class => ReceiptFactory::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->bindings);
    }
}
