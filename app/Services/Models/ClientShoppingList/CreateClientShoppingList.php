<?php

namespace App\Services\Models\ClientShoppingList;

use App\Contracts\Models\ClientShoppingList\CreateClientShoppingListContract;
use App\Models\Client;
use App\Models\ClientShoppingList;
use Illuminate\Support\Facades\DB;

class CreateClientShoppingList implements CreateClientShoppingListContract
{
    /**
     * @param Client $client
     * @param string $name
     * @param array $assortments
     * @return ClientShoppingList
     */
    public function create(Client $client, string $name, array $assortments = []): ClientShoppingList
    {
        $customerList = resolve(ClientShoppingList::class);
        $customerList->client_uuid = $client->uuid;
        $customerList->name = $name;

        DB::transaction(function () use ($customerList, $assortments) {
            $customerList->save();
            if ($assortments) {
                $customerList->assortments()->sync($assortments);
            }
        });

        return $customerList;
    }
}