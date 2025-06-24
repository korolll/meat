<?php

namespace App\Policies;

use App\Models\LaboratoryTest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaboratoryTestPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->is_laboratory;
    }

    /**
     * @param User $user
     * @param LaboratoryTest $laboratoryTest
     * @return bool
     */
    public function view(User $user, LaboratoryTest $laboratoryTest)
    {
        return $user->is_laboratory && $laboratoryTest->is_new;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_store || $user->is_distribution_center || $user->is_supplier;
    }

    /**
     * @param User $user
     * @param LaboratoryTest $laboratoryTest
     * @return bool
     */
    public function update(User $user, LaboratoryTest $laboratoryTest)
    {
        return $laboratoryTest->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @param LaboratoryTest $laboratoryTest
     * @return bool
     */
    public function setInWork(User $user, LaboratoryTest $laboratoryTest)
    {
        return $user->is_laboratory && $laboratoryTest->is_new;
    }
}
