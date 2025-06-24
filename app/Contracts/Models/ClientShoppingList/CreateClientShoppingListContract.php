<?php

namespace App\Contracts\Models\ClientShoppingList;

use App\Models\Client;
use App\Models\ClientShoppingList;

interface CreateClientShoppingListContract
{
    /**
     * @param Client $client
     * @param string $name
     * @param array $assortments
     * @return ClientShoppingList
     */
    public function create(Client $client, string $name, array $assortments = []): ClientShoppingList;
}