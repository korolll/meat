<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PromoFavoriteAssortmentSettingPolicy
{
    use HandlesAuthorization;

    /**
     * @param Authenticatable $actor
     *
     * @return bool
     */
    public function any(Authenticatable $actor): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }
}
