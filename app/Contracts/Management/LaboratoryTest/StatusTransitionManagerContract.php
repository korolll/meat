<?php

namespace App\Contracts\Management\LaboratoryTest;

use App\Models\LaboratoryTest;
use App\Models\User;

interface StatusTransitionManagerContract
{
    /**
     * @param User $user
     * @param string $nextStatusId
     * @return LaboratoryTest
     */
    public function transition(User $user, string $nextStatusId): LaboratoryTest;
}
