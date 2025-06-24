<?php

namespace App\Policies;

use App\Models\Stocktaking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StocktakingPolicy
{
    use HandlesAuthorization;

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
     * @param Stocktaking $stocktaking
     * @return bool
     */
    public function view(User $user, Stocktaking $stocktaking)
    {
        return $stocktaking->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_store;
    }

    /**
     * @param User $user
     * @param Stocktaking $stocktaking
     * @return bool
     */
    public function update(User $user, Stocktaking $stocktaking)
    {
        return !$stocktaking->is_approved && $stocktaking->user_uuid === $user->uuid;
    }
}
