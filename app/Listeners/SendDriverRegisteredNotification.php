<?php

namespace App\Listeners;

use App\Events\DriverRegistered;
use App\Notifications\Drivers\API\DriverRegistered as DriverRegisteredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDriverRegisteredNotification implements ShouldQueue
{
    /**
     * @param DriverRegistered $event
     */
    public function handle(DriverRegistered $event)
    {
        $notification = DriverRegisteredNotification::make($event->driver, $event->password);
        $event->driver->notify($notification);
    }
}
