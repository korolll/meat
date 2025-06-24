<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\PromoDiverseFoodSettings;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PromoDiverseFoodSettingsPolicy
{
    use HandlesAuthorization;

    public function any(Authenticatable $actor): bool
    {
        return $this->actorIsAdmin($actor);
    }

    public function index(Authenticatable $actor): bool
    {
        return true;
    }

    public function view(Authenticatable $actor, PromoDiverseFoodSettings $settings): bool
    {
        if ($actor instanceof Client) {
            return $settings->is_enabled;
        }

        return true;
    }

    private function actorIsAdmin(Authenticatable $actor): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }
        return false;
    }
}
