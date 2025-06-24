<?php

namespace App\Policies;

use App\Models\AppContact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AppContactPolicy
{
    use HandlesAuthorization;

    /**
     * @param Authenticatable $actor
     * @return bool
     */
    public function index(Authenticatable $actor)
    {
        return true;
    }

    /**
     * @param User $user
     * @param Onboarding $onboarding
     * @return bool
     */
    public function update(User $user)
    {
        return $user->is_admin;
    }
}
