<?php

namespace App\Policies;

use App\Models\Vacancy;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class VacancyPolicy
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
     * @param Vacancy $vacancy
     * @return bool
     */
    public function view(User $user, Vacancy $vacancy)
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
     * @param Vacancy $vacancy
     * @return bool
     */
    public function update(User $user, Vacancy $vacancy)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param Vacancy $vacancy
     * @return bool
     */
    public function delete(User $user, Vacancy $vacancy)
    {
        return $user->is_admin;
    }
}
