<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * @param User $user
     */
    public function saving(User $user)
    {
        $user->email = mb_strtolower($user->email);
    }
}
