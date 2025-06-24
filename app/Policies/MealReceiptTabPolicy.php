<?php

namespace App\Policies;

use App\Models\MealReceiptTab;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MealReceiptTabPolicy
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
     * @param \App\Models\MealReceiptTab       $mealReceiptTab
     *
     * @return bool
     */
    public function view(Authenticatable $actor, MealReceiptTab $mealReceiptTab): bool
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
     * @param \App\Models\MealReceiptTab       $mealReceiptTab
     *
     * @return bool
     */
    public function update(Authenticatable $actor, MealReceiptTab $mealReceiptTab): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     * @param \App\Models\MealReceiptTab       $mealReceiptTab
     *
     * @return bool
     */
    public function delete(Authenticatable $actor, MealReceiptTab $mealReceiptTab): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }
}
