<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\API\UserEmailConfirmation as UserEmailConfirmationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendUserEmailConfirmationNotification implements ShouldQueue
{
    /**
     * @param UserRegistered $event
     */
    public function handle(UserRegistered $event)
    {
        $notification = UserEmailConfirmationNotification::make($event->user);
        $event->user->notify($notification);
    }
}
