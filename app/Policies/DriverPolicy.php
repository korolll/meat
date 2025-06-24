<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DriverPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function indexOwned(User $user)
    {
        return $user->is_delivery_service;
    }

    /**
     * @param User $user
     * @param Driver $driver
     * @return bool
     */
    public function view(User $user, Driver $driver)
    {
        return $driver->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_delivery_service;
    }

    /**
     * @param User $user
     * @param Driver $driver
     * @return bool
     */
    public function update(User $user, Driver $driver)
    {
        return $driver->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @param Driver $driver
     * @return bool
     */
    public function delete(User $user, Driver $driver)
    {
        return $driver->user_uuid === $user->uuid;
    }
}
