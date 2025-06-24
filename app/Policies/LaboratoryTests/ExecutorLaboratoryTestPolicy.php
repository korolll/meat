<?php

namespace App\Policies\LaboratoryTests;

use App\Models\LaboratoryTests\ExecutorLaboratoryTest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExecutorLaboratoryTestPolicy
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
     * @param ExecutorLaboratoryTest $executorLaboratoryTest
     * @return bool
     */
    public function view(User $user, ExecutorLaboratoryTest $executorLaboratoryTest)
    {
        return $user->uuid === $executorLaboratoryTest->executor_user_uuid;
    }

    /**
     * @param User $user
     * @param ExecutorLaboratoryTest $executorLaboratoryTest
     * @return bool
     */
    public function setStatus(User $user, ExecutorLaboratoryTest $executorLaboratoryTest)
    {
        return $user->uuid === $executorLaboratoryTest->executor_user_uuid;
    }
}
