<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class RatingScorePolicy
{
    use HandlesAuthorization;

    /**
     * @param Authenticatable $actor
     * @return bool
     */
    public function findForAssortmentsByClients(Authenticatable $actor)
    {
        if ($actor instanceof Client) {
            return true;
        }

        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }
}
