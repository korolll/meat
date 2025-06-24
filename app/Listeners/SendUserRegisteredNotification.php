<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\UserType;
use App\Notifications\API\UserRegistered as UserRegisteredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendUserRegisteredNotification implements ShouldQueue
{
    /**
     * @param UserRegistered $event
     */
    public function handle(UserRegistered $event)
    {
        $user = $event->user;

        if (in_array($user->user_type_id, UserType::USER_AVAILABLE_IDS)) {
            $to = config('app.mail.admin_emails');

            foreach ($to as $email) {
                Notification::route('mail', $email)->notify(new UserRegisteredNotification($user));
            }
        }
    }
}
