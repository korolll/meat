<?php

namespace App\Policies;

use App\Models\LaboratoryTestAppealType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaboratoryTestAppealTypePolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * @param User $user
     * @param LaboratoryTestAppealType $laboratoryTestAppealType
     * @return bool
     */
    public function view(User $user, LaboratoryTestAppealType $laboratoryTestAppealType)
    {
        return true;
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
     * @param LaboratoryTestAppealType $laboratoryTestAppealType
     * @return bool
     */
    public function update(User $user, LaboratoryTestAppealType $laboratoryTestAppealType)
    {
        return $user->is_admin;
    }


    /**
     * @param User $user
     * @param LaboratoryTestAppealType $laboratoryTestAppealType
     * @return bool
     */
    public function delete(User $user, LaboratoryTestAppealType $laboratoryTestAppealType)
    {
        return $user->is_admin;
    }
}
