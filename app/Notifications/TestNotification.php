<?php

namespace App\Notifications;

use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification implements ShouldQueue
{
    use HasStaticMakeMethod, Queueable;

    /**
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['sms'];
    }

    /**
     * @param mixed $notifiable
     * @return string
     */
    public function toSms($notifiable)
    {
        return 'Тестовое сообщение';
    }
}
