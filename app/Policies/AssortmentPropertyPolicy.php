<?php

namespace App\Policies;

use App\Models\AssortmentProperty;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssortmentPropertyPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * @param User $user
     * @param AssortmentProperty $assortmentProperty
     * @return bool
     */
    public function view(User $user, AssortmentProperty $assortmentProperty)
    {
        return true;
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
     * @param AssortmentProperty $assortmentProperty
     * @return bool
     */
    public function update(User $user, AssortmentProperty $assortmentProperty)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param AssortmentProperty $assortmentProperty
     * @return bool
     */
    public function delete(User $user, AssortmentProperty $assortmentProperty)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param AssortmentProperty $assortmentProperty
     * @return bool
     */
    public function addAvailableValue(User $user, AssortmentProperty $assortmentProperty)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param AssortmentProperty $assortmentProperty
     * @return bool
     */
    public function removeAvailableValue(User $user, AssortmentProperty $assortmentProperty)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param AssortmentProperty $assortmentProperty
     * @return bool
     */
    public function changeDataType(User $user, AssortmentProperty $assortmentProperty)
    {
        return $user->is_admin;
    }
}
