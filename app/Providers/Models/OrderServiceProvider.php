<?php

namespace App\Providers\Models;

use App\Services\Models\Order\CashFileController;
use App\Services\Models\Order\CashFileControllerInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        CashFileControllerInterface::class => CashFileController::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
