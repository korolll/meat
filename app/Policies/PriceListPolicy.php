<?php

namespace App\Policies;

use App\Models\PriceList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PriceListPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function indexOwned(User $user)
    {
        return $user->is_distribution_center || $user->is_supplier || $user->is_store;
    }

    /**
     * @param User $user
     * @param PriceList $priceList
     * @return bool
     */
    public function view(User $user, PriceList $priceList)
    {
        return $priceList->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_distribution_center || $user->is_supplier || $user->is_store;
    }

    /**
     * @param User $user
     * @param PriceList $priceList
     * @return bool
     */
    public function update(User $user, PriceList $priceList)
    {
        return $priceList->is_future && $priceList->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @param PriceList $priceList
     * @return bool
     */
    public function delete(User $user, PriceList $priceList)
    {
        return $priceList->is_future && $priceList->user_uuid === $user->uuid;
    }
}
