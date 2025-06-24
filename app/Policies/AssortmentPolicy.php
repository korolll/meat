<?php

namespace App\Policies;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AssortmentPolicy
{
    use HandlesAuthorization;

    /**
     * @param Authenticatable $actor
     * @return bool
     */
    public function index(Authenticatable $actor)
    {
        if ($actor instanceof Client) {
            return true;
        }

        if ($actor instanceof User) {
            return $actor->is_admin || $actor->is_distribution_center || $actor->is_store || $actor->is_supplier;
        }

        return false;
    }

    /**
     * @param Authenticatable $actor
     * @param Assortment $assortment
     * @return bool
     */
    public function view(Authenticatable $actor, Assortment $assortment)
    {
        if ($actor instanceof User && $actor->is_admin) {
            return true;
        }

        return $assortment->is_approved;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_distribution_center || $user->is_supplier || $user->is_admin;
    }

    /**
     * @param User $user
     * @param Assortment $assortment
     * @return bool
     */
    public function update(User $user, Assortment $assortment)
    {
        if ($user->is_admin) {
            return true;
        }

        if ($assortment->is_approved) {
            return false;
        }

        return $user->is_distribution_center || $user->is_supplier;
    }

    /**
     * @param User $user
     * @param Assortment $assortment
     * @return bool
     */
    public function verify(User $user, Assortment $assortment)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function assortmentMatrixIndex(User $user)
    {
        return $user->is_distribution_center || $user->is_store || $user->is_supplier;
    }

    /**
     * @param User $user
     * @param Assortment $assortment
     * @return bool
     */
    public function assortmentMatrixAttach(User $user, Assortment $assortment)
    {
        if (!$assortment->is_approved) {
            return false;
        }

        return $user->is_distribution_center || $user->is_store || $user->is_supplier;
    }

    /**
     * @param User $user
     * @param Assortment $assortment
     * @return bool
     */
    public function assortmentMatrixDetach(User $user, Assortment $assortment)
    {
        return $user->is_distribution_center || $user->is_store || $user->is_supplier;
    }
}
