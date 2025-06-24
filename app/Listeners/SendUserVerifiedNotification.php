<?php

namespace App\Listeners;

use App\Events\UserVerified;
use App\Notifications\API\UserVerified as UserVerifiedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendUserVerifiedNotification implements ShouldQueue
{
    /**
     * @param UserVerified $event
     */
    public function handle(UserVerified $event)
    {
        $notification = UserVerifiedNotification::make($event->user, $event->comment);
        $event->user->notify($notification);
    }
}
