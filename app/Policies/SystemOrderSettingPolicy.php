<?php

namespace App\Policies;

use App\Models\SystemOrderSetting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemOrderSettingPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param SystemOrderSetting $systemOrderSetting
     * @return bool
     */
    public function view(User $user, SystemOrderSetting $systemOrderSetting)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param SystemOrderSetting $systemOrderSetting
     * @return bool
     */
    public function update(User $user, SystemOrderSetting $systemOrderSetting)
    {
        return $user->is_admin;
    }
}
