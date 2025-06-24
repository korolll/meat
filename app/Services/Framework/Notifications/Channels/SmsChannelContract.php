<?php

namespace App\Services\Framework\Notifications\Channels;

use Illuminate\Notifications\Notification;

interface SmsChannelContract
{
    /**
     * @param mixed $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification);
}
