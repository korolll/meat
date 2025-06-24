<?php

namespace App\Policies;

use App\Models\PromoDescription;
use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PromoDescriptionPolicy
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
     * @param PromoDescription $promoDescription
     * @return bool
     */
    public function view(User $user, PromoDescription $promoDescription)
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
     * @param PromoDescription $promoDescription
     * @return bool
     */
    public function update(User $user, PromoDescription $promoDescription)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param PromoDescription $promoDescription
     * @return bool
     */
    public function delete(User $user, PromoDescription $promoDescription)
    {
        return $user->is_admin;
    }
}
