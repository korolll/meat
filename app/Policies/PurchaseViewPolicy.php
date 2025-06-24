<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseViewPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     *
     * @return bool
     */
    public function actionsReport(User $user)
    {
        return $user->is_store || $user->is_admin;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function report(User $user)
    {
        return $user->is_store || $user->is_admin;
    }
}
