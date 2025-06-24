<?php

namespace App\Policies;

use App\Models\StoryTab;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class StoryTabPolicy
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
     * @param \App\Models\StoryTab             $storyTab
     *
     * @return bool
     */
    public function view(Authenticatable $actor, StoryTab $storyTab): bool
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
     * @param \App\Models\StoryTab $storyTab
     *
     * @return bool
     */
    public function update(Authenticatable $actor, StoryTab $storyTab): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     * @param \App\Models\StoryTab             $storyTab
     *
     * @return bool
     */
    public function delete(Authenticatable $actor, StoryTab $storyTab): bool
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        return false;
    }
}
