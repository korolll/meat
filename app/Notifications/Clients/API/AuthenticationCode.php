<?php

namespace App\Notifications\Clients\API;

use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AuthenticationCode extends Notification
{
    use HasStaticMakeMethod, Queueable;

    /**
     * @var string
     */
    public $code;

    /**
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

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
        return "Проверочный код: {$this->code}";
    }
}
