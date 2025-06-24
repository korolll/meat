<?php

namespace App\Policies;

use App\Models\Catalog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CatalogPolicy
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
     * @param User $user
     * @return bool
     */
    public function indexOwned(User $user)
    {
        return $user->is_admin || $user->is_distribution_center || $user->is_supplier || $user->is_store;
    }

    /**
     * @param User $user
     * @param Catalog $catalog
     * @return bool
     */
    public function view(User $user, Catalog $catalog)
    {
        if ($catalog->is_public) {
            return $user->is_admin || $user->is_distribution_center || $user->is_supplier || $user->is_store;
        }

        return $catalog->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_admin || $user->is_distribution_center || $user->is_supplier || $user->is_store;
    }

    /**
     * @param User $user
     * @param Catalog $catalog
     * @return bool
     */
    public function update(User $user, Catalog $catalog)
    {
        if ($catalog->is_public) {
            return $user->is_admin;
        }

        return $catalog->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @param Catalog $catalog
     * @return bool
     */
    public function delete(User $user, Catalog $catalog)
    {
        if ($catalog->is_public) {
            return $user->is_admin;
        }

        return $catalog->user_uuid === $user->uuid;
    }
}
