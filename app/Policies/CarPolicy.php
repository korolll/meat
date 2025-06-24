<?php

namespace App\Policies;

use App\Models\Car;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CarPolicy
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
     * @param Car $car
     * @return bool
     */
    public function view(User $user, Car $car)
    {
        return $car->user_uuid === $user->uuid;
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
     * @param Car $car
     * @return bool
     */
    public function update(User $user, Car $car)
    {
        return $car->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @param Car $car
     * @return bool
     */
    public function delete(User $user, Car $car)
    {
        return $car->user_uuid === $user->uuid;
    }
}
