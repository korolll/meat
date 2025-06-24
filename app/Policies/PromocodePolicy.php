<?php

namespace App\Policies;

use App\Models\Promocode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PromocodePolicy
{
    use HandlesAuthorization;

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     *
     * @return bool
     */
    public function index(Authenticatable $actor): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     * @param \App\Models\Promocode            $promocode
     *
     * @return bool
     */
    public function view(Authenticatable $actor, Promocode $promocode): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     *
     * @return bool
     */
    public function create(Authenticatable $actor): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     * @param \App\Models\Promocode               $promocode
     *
     * @return bool
     */
    public function update(Authenticatable $actor, Promocode $promocode): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     * @param \App\Models\Promocode               $promocode
     *
     * @return bool
     */
    public function delete(Authenticatable $actor, Promocode $promocode): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }
}
