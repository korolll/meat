<?php

namespace App\Policies\ProductRequests;

use App\Models\Driver;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DeliveryProductRequestPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function indexMonitoring(User $user)
    {
        return $user->is_delivery_service;
    }

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
     * @param ProductRequest $productRequest
     * @return bool
     */
    public function view(Authenticatable $actor, ProductRequest $productRequest)
    {
        if ($actor instanceof User) {
            if ($productRequest->is_waiting_for_delivery) {
                return $actor->is_delivery_service;
            }

            return $productRequest->delivery_user_uuid === $actor->uuid;
        }

        if ($actor instanceof Driver) {
            $transportation = $productRequest->transportation;

            return $transportation && $transportation->driver_uuid === $actor->uuid;
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
}
