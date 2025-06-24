<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class OrderPolicy
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
     *
     * @return bool
     */
    public function indexOwned(Authenticatable $actor): bool
    {
        if ($actor instanceof Client) {
            return true;
        }

        if ($actor instanceof User) {
            return $actor->user_type_id === UserType::ID_STORE;
        }

        return false;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     * @param \App\Models\Order                $order
     *
     * @return bool
     */
    public function view(Authenticatable $actor, Order $order): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin || $actor->user_type_id === UserType::ID_STORE;
        }

        if ($actor instanceof Client) {
            return $order->client_uuid === $actor->uuid;
        }

        return false;
    }

    /**
     * @param \App\Models\Client $client
     *
     * @return bool
     */
    public function create(Client $client): bool
    {
        return $this->calculate($client);
    }

    /**
     * @param \App\Models\Client $client
     *
     * @return bool
     */
    public function calculate(Client $client): bool
    {
        return true;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $user
     * @param \App\Models\Order                $order
     *
     * @return bool
     */
    public function update(Authenticatable $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Order $order
     *
     * @return bool
     */
    public function retryPayment(User $user, Order $order): bool
    {
        return $user->is_admin;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $user
     * @param \App\Models\Order                $order
     *
     * @return bool
     */
    public function setStatus(Authenticatable $user, Order $order): bool
    {
        return $this->update($user, $order);
    }
}
