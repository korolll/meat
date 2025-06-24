<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->is_admin || $user->is_supplier || $user->is_distribution_center;
    }

    /**
     * @param Authenticatable $actor
     * @param User $model
     * @return bool
     */
    public function view(Authenticatable $actor, User $model)
    {
        if ($actor instanceof Client) {
            return $model->is_store;
        }
        if ($actor instanceof User) {
            return $actor->is_admin;
        }
        return false;
    }

    /**
     * @param Authenticatable $actor
     * @param User $model
     * @return bool
     */
    public function update(Authenticatable $actor, User $model)
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }

    /**
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function verify(User $user, User $model)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function supplyContract(User $user)
    {
        return $user->is_store;
    }

    public function importProductRequests(User $user)
    {
        return $user->is_admin;
    }
}
