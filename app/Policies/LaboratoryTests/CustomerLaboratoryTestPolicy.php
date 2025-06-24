<?php

namespace App\Policies\LaboratoryTests;

use App\Models\LaboratoryTests\CustomerLaboratoryTest;
use App\Models\LaboratoryTestStatus;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerLaboratoryTestPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->is_store || $user->is_distribution_center || $user->is_supplier;
    }

    /**
     * @param User $user
     * @param CustomerLaboratoryTest $customerLaboratoryTest
     * @return bool
     */
    public function view(User $user, CustomerLaboratoryTest $customerLaboratoryTest)
    {
        return $user->uuid === $customerLaboratoryTest->customer_user_uuid;
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
     * @param CustomerLaboratoryTest $customerLaboratoryTest
     * @return bool
     */
    public function update(User $user, CustomerLaboratoryTest $customerLaboratoryTest)
    {
        return $user->uuid === $customerLaboratoryTest->customer_user_uuid &&
            $customerLaboratoryTest->laboratory_test_status_id === LaboratoryTestStatus::ID_CREATED;
    }

    /**
     * @param User $user
     * @param CustomerLaboratoryTest $customerLaboratoryTest
     * @return bool
     */
    public function setStatus(User $user, CustomerLaboratoryTest $customerLaboratoryTest)
    {
        return $user->uuid === $customerLaboratoryTest->customer_user_uuid;
    }
}
