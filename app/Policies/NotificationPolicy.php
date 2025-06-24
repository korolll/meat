<?php

namespace App\Policies;

use App\Models\Client;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\Client                             $сlient
     * @param \Illuminate\Notifications\DatabaseNotification $notification
     *
     * @return bool
     */
    public function read(Client $client, DatabaseNotification $notification)
    {
        return $notification->notifiable_type === $client->getMorphClass() && $notification->notifiable_uuid === $client->uuid;
    }

    public function delete(Client $client, DatabaseNotification $notification)
    {
        return $notification->notifiable_type === $client->getMorphClass() && $notification->notifiable_uuid === $client->uuid;
    }
}
