<?php

namespace App\Providers\Models;

use App\Contracts\Models\ClientShoppingList\CreateClientShoppingListContract;
use App\Services\Models\ClientShoppingList\CreateClientShoppingList;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ClientShoppingListServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        CreateClientShoppingListContract::class => CreateClientShoppingList::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
