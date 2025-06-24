<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use App\Models\ClientShoppingList;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Client $client
     * @return bool
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * @param Client $client
     * @return bool
     */
    public function favoriteAssortmentAttach(Client $client)
    {
        return true;
    }

    /**
     * @param Client $client
     * @return bool
     */
    public function favoriteAssortmentDetach(Client $client)
    {
        return true;
    }
}
