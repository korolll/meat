<?php

namespace App\Services\Framework\Notifications\Channels;

use App\Services\Framework\Notifications\Messages\SmsMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;

abstract class SmsChannel implements SmsChannelContract
{
    /**
     * @param mixed $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        if (empty($recipients = $this->getRecipients($notifiable, $notification))) {
            return;
        }

        if (empty($message = $this->getMessage($notifiable, $notification))) {
            return;
        }

        foreach ($recipients as $to) {
            $this->sendMessage($to, $message);
        }
    }

    /**
     * @param string $to
     * @param SmsMessage $message
     */
    abstract protected function sendMessage($to, SmsMessage $message);

    /**
     * @param mixed $notifiable
     * @param Notification $notification
     * @return array
     */
    protected function getRecipients($notifiable, Notification $notification)
    {
        $to = $notifiable->routeNotificationFor('sms', $notification);

        return empty($to) ? [] : Arr::wrap($to);
    }

    /**
     * @param mixed $notifiable
     * @param Notification $notification
     * @return SmsMessage
     */
    protected function getMessage($notifiable, Notification $notification)
    {
        $message = call_user_func([$notification, 'toSms'], $notifiable);

        if (is_string($message)) {
            $message = SmsMessage::make($message);
        }

        return $message;
    }
}
