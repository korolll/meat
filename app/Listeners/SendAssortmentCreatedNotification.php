<?php

namespace App\Listeners;

use App\Events\AssortmentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\API\AssortmentCreated as AssortmentCreatedNotification;

class SendAssortmentCreatedNotification implements ShouldQueue
{
    /**
     * @param AssortmentCreated $event
     */
    public function handle(AssortmentCreated $event)
    {
        $to = config('app.mail.admin_emails');

        foreach ($to as $email) {
            Notification::route('mail', $email)->notify(new AssortmentCreatedNotification($event->assortment));
        }
    }
}
