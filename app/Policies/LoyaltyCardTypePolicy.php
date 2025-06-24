<?php

namespace App\Policies;

use App\Models\LoyaltyCardType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoyaltyCardTypePolicy
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
     * @param User $user
     * @return bool
     */
    public function indexOwned(User $user)
    {
        return $user->is_store;
    }

    /**
     * @param User $user
     * @param User $owner
     * @return bool
     */
    public function indexOwnedBy(User $user, User $owner)
    {
        return $user->is_admin && $owner->is_store;
    }

    /**
     * @param User $user
     * @param LoyaltyCardType $loyaltyCardType
     * @return bool
     */
    public function view(User $user, LoyaltyCardType $loyaltyCardType)
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
     * @param LoyaltyCardType $loyaltyCardType
     * @return bool
     */
    public function update(User $user, LoyaltyCardType $loyaltyCardType)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param LoyaltyCardType $loyaltyCardType
     * @param User $owner
     * @return bool
     */
    public function attachTo(User $user, LoyaltyCardType $loyaltyCardType, User $owner)
    {
        return $user->is_admin && $owner->is_store;
    }

    /**
     * @param User $user
     * @param LoyaltyCardType $loyaltyCardType
     * @param User $owner
     * @return bool|mixed
     */
    public function detachFrom(User $user, LoyaltyCardType $loyaltyCardType, User $owner)
    {
        return $user->is_admin;
    }
}
