<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class OrderProductPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\User $actor
     *
     * @return bool
     */
    public function index(User $actor): bool
    {
        return $actor->is_admin;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     * @param \App\Models\OrderProduct         $orderProduct
     *
     * @return bool
     */
    public function view(Authenticatable $actor, OrderProduct $orderProduct): bool
    {
        if ($actor instanceof User) {
            if ($actor->is_admin) {
                return true;
            }

            if ($actor->user_type_id === UserType::ID_STORE) {
                return $actor->uuid === $orderProduct->order->store_user_uuid;
            }
        }

        if ($actor instanceof Client) {
            return $actor->uuid === $orderProduct->order->client_uuid;
        }

        return false;
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Order $order
     *
     * @return bool
     */
    public function create(User $user, Order $order): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($user->user_type_id === UserType::ID_STORE) {
            return $user->uuid === $order->store_user_uuid;
        }

        return false;
    }

    /**
     * @param \App\Models\User         $user
     * @param \App\Models\OrderProduct $orderProduct
     *
     * @return bool
     */
    public function update(User $user, OrderProduct $orderProduct): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($user->user_type_id === UserType::ID_STORE) {
            return $user->uuid === $orderProduct->order->store_user_uuid;
        }

        return false;
    }

    /**
     * @param \App\Models\Client       $client
     * @param \App\Models\OrderProduct $orderProduct
     *
     * @return bool
     */
    public function setRating(Client $client, OrderProduct $orderProduct): bool
    {
        return $client->uuid === $orderProduct->order->client_uuid;
    }
}
