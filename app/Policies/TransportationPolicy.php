<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\Transportation;
use App\Models\TransportationPoint;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TransportationPolicy
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
     * @param Authenticatable $actor
     * @param Transportation $transportation
     * @return bool
     */
    public function view(Authenticatable $actor, Transportation $transportation)
    {
        if ($actor instanceof User) {
            return $transportation->user_uuid === $actor->uuid;
        }

        if ($actor instanceof Driver) {
            return $transportation->driver_uuid === $actor->uuid;
        }

        return false;
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
     * @param Transportation $transportation
     * @return bool
     */
    public function update(User $user, Transportation $transportation)
    {
        if ($transportation->is_done) {
            return false;
        }

        return $transportation->user_uuid === $user->uuid;
    }

    /**
     * @param Authenticatable $actor
     * @param Transportation $transportation
     * @return bool
     */
    public function setStarted(Authenticatable $actor, Transportation $transportation)
    {
        if ($transportation->is_on_the_way) {
            return false;
        }

        if ($actor instanceof User) {
            return $transportation->user_uuid === $actor->uuid;
        }

        if ($actor instanceof Driver) {
            return $transportation->driver_uuid === $actor->uuid;
        }

        return false;
    }

    /**
     * @param Authenticatable $actor
     * @param Transportation $transportation
     * @param TransportationPoint $point
     * @return bool
     */
    public function setArrived(Authenticatable $actor, Transportation $transportation, TransportationPoint $point)
    {
        if (!$transportation->is_on_the_way || $point->is_visited) {
            return false;
        }

        if ($actor instanceof User) {
            return $transportation->user_uuid === $actor->uuid;
        }

        if ($actor instanceof Driver) {
            return $transportation->driver_uuid === $actor->uuid;
        }

        return false;
    }
}
