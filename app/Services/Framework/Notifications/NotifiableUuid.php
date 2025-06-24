<?php

namespace App\Services\Framework\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\RoutesNotifications;

trait NotifiableUuid
{
    use RoutesNotifications;

    /**
     * Next part from (except notifications, that method is overridden)
     *
     * @see HasDatabaseNotifications
     *
     */

    /**
     * Get the entity's notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this;
        return $model->morphMany(DatabaseNotification::class, 'notifiable', 'notifiable_type', 'notifiable_uuid')->orderBy('created_at', 'desc');
    }

    /**
     * Get the entity's read notifications.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function readNotifications()
    {
        return $this->notifications()->read();
    }

    /**
     * Get the entity's unread notifications.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }
}
