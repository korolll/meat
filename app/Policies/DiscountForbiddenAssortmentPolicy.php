<?php

namespace App\Policies;

use App\Models\DiscountForbiddenAssortment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DiscountForbiddenAssortmentPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function index(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param DiscountForbiddenAssortment $assortment
     *
     * @return bool
     */
    public function view(User $user, DiscountForbiddenAssortment $assortment)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param DiscountForbiddenAssortment $assortment
     *
     * @return bool
     */
    public function delete(User $user, DiscountForbiddenAssortment $assortment)
    {
        return $user->is_admin;
    }
}
