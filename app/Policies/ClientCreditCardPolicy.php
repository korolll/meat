<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\ClientCreditCard;
use App\Models\ClientShoppingList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ClientCreditCardPolicy
{
    use HandlesAuthorization;

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     *
     * @return bool
     */
    public function index(Authenticatable $actor): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }

    /**
     * @param \App\Models\Client $client
     *
     * @return bool
     */
    public function indexOwned(Client $client): bool
    {
        return true;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     * @param \App\Models\ClientCreditCard     $card
     *
     * @return bool
     */
    public function view(Authenticatable $actor, ClientCreditCard $card): bool
    {
        if ($actor instanceof Client) {
            return $card->client_uuid === $actor->uuid;
        }

        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }

    /**
     * @param \App\Models\Client           $client
     * @param \App\Models\ClientCreditCard $card
     *
     * @return bool
     */
    public function delete(Client $client, ClientCreditCard $card): bool
    {
        return $card->client_uuid === $client->uuid;
    }
}
