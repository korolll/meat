<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TagPolicy
{
    use HandlesAuthorization;

    /**
     * @param Authenticatable $actor
     * @return bool
     */
    public function index(Authenticatable $actor)
    {
        return true;
    }

    /**
     * @param Authenticatable $actor
     * @param Tag $tag
     * @return bool
     */
    public function view(Authenticatable $actor, Tag $tag)
    {
        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param Tag $tag
     * @return bool
     */
    public function update(User $user, Tag $tag)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param Tag $tag
     * @return bool
     */
    public function delete(User $user, Tag $tag)
    {
        return $user->is_admin;
    }

    /**
     * @param Authenticatable $actor
     * @return bool
     */
    public function search(Authenticatable $actor)
    {
        return true;
    }
}
