<?php

namespace App\Policies;

use App\Models\PromoYellowPrice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PromoYellowPricePolicy
{
    use HandlesAuthorization;

    public function create(Authenticatable $actor): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }
        return false;
    }

    public function index(Authenticatable $actor): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }
        return false;
    }

    public function update(Authenticatable $actor, PromoYellowPrice $model): bool
    {
        if (!$actor instanceof User || !$actor->is_admin) {
            return false;
        }
        if ($model->start_at <= now()) {
            return false;
        }
        return true;
    }

    public function show(Authenticatable $actor, PromoYellowPrice $model)
    {
        if (!$actor instanceof User || !$actor->is_admin) {
            return false;
        }
        return true;
    }

    public function destroy(Authenticatable $actor, PromoYellowPrice $model)
    {
        if (!$actor instanceof User || !$actor->is_admin) {
            return false;
        }
        return true;
    }

    public function toggle(Authenticatable $actor, PromoYellowPrice $model)
    {
        if (!$actor instanceof User || !$actor->is_admin) {
            return false;
        }

        return true;
    }
}
