<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\ClientBonusTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ClientBonusTransactionPolicy
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
     * @param \Illuminate\Foundation\Auth\User   $actor
     * @param \App\Models\ClientBonusTransaction $transaction
     *
     * @return bool
     */
    public function view(Authenticatable $actor, ClientBonusTransaction $transaction): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }
        if ($actor instanceof Client) {
            return $actor->uuid === $transaction->client_uuid;
        }

        return false;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     *
     * @return bool
     */
    public function create(Authenticatable $actor): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }
}
