<?php

namespace App\Policies;

use App\Models\NotificationTask;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationTaskPolicy
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
     * @param \App\Models\User             $actor
     * @param \App\Models\NotificationTask $notificationTask
     *
     * @return bool
     */
    public function view(User $actor, NotificationTask $notificationTask): bool
    {
        return $actor->is_admin;
    }

    /**
     * @param \App\Models\User $actor
     *
     * @return bool
     */
    public function create(User $actor): bool
    {
        return $actor->is_admin;
    }

    /**
     * @param \App\Models\User             $actor
     * @param \App\Models\NotificationTask $notificationTask
     *
     * @return bool
     */
    public function update(User $actor, NotificationTask $notificationTask): bool
    {
        return $actor->is_admin;
    }

    /**
     * @param \App\Models\User             $actor
     * @param \App\Models\NotificationTask $notificationTask
     *
     * @return bool
     */
    public function delete(User $actor, NotificationTask $notificationTask): bool
    {
        return $actor->is_admin;
    }
}
