<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\LoyaltyCard;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoyaltyCardPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param Client $client
     * @return bool
     */
    public function indexOwned(Client $client)
    {
        return true;
    }

    /**
     * @param User $user
     * @param LoyaltyCard $loyaltyCard
     * @return bool
     */
    public function view(User $user, LoyaltyCard $loyaltyCard)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param LoyaltyCard $loyaltyCard
     * @return bool
     */
    public function update(User $user, LoyaltyCard $loyaltyCard)
    {
        return $user->is_admin;
    }

    /**
     * @param Client $client
     * @param LoyaltyCard $loyaltyCard
     * @return bool
     */
    public function attach(Client $client, LoyaltyCard $loyaltyCard)
    {
        return $loyaltyCard->client_uuid === null;
    }

    /**
     * @param Client $client
     * @param LoyaltyCard $loyaltyCard
     * @return bool
     */
    public function detach(Client $client, LoyaltyCard $loyaltyCard)
    {
        return $loyaltyCard->client_uuid === $client->uuid;
    }
}
