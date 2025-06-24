<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\ClientShoppingList;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientShoppingListPolicy
{
    use HandlesAuthorization;

    /**
     * @param Client $client
     * @return bool
     */
    public function create(Client $client)
    {
        return true;
    }

    /**
     * @param Client $client
     * @return bool
     */
    public function index(Client $client)
    {
        return true;
    }

    /**
     * @param Client $client
     * @param ClientShoppingList $shoppingList
     * @return bool
     */
    public function show(Client $client, ClientShoppingList $shoppingList)
    {
        return $shoppingList->client_uuid === $client->uuid;
    }

    /**
     * @param Client $client
     * @param ClientShoppingList $shoppingList
     * @return bool
     */
    public function update(Client $client, ClientShoppingList $shoppingList)
    {
        return $shoppingList->client_uuid === $client->uuid;
    }

    /**
     * @param Client $client
     * @param ClientShoppingList $shoppingList
     * @return bool
     */
    public function delete(Client $client, ClientShoppingList $shoppingList)
    {
        return $shoppingList->client_uuid === $client->uuid;
    }

    /**
     * @param Client $client
     * @param ClientShoppingList $shoppingList
     * @return bool
     */
    public function attachAssortment(Client $client, ClientShoppingList $shoppingList)
    {
        return $shoppingList->client_uuid === $client->uuid;
    }

    /**
     * @param Client $client
     * @param ClientShoppingList $shoppingList
     * @return bool
     */
    public function detachAssortment(Client $client, ClientShoppingList $shoppingList)
    {
        return $shoppingList->client_uuid === $client->uuid;
    }
}
