<?php

namespace App\Policies;

use App\Models\Social;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SocialPolicy
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
     * @param Social $social
     * @return bool
     */
    public function view(User $user, Social $social)
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
     * @param Social $social
     * @return bool
     */
    public function update(User $user, Social $social)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param Social $social
     * @return bool
     */
    public function delete(User $user, Social $social)
    {
        return $user->is_admin;
    }
}
