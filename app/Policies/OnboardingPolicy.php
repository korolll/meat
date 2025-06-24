<?php

namespace App\Policies;

use App\Models\Onboarding;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class OnboardingPolicy
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
    public function view(User $user, Onboarding $onboarding)
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
     * @param Onboarding $onboarding
     * @return bool
     */
    public function update(User $user, Onboarding $onboarding)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param Onboarding $onboarding
     * @return bool
     */
    public function delete(User $user, Onboarding $onboarding)
    {
        return $user->is_admin;
    }
}
