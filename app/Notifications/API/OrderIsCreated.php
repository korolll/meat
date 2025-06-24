<?php

namespace App\Notifications\API;

use App\Models\Order;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderIsCreated extends Notification
{
    use HasStaticMakeMethod, Queueable;

    /**
     * @var \App\Models\Order
     */
    public Order $order;

    /**
     * @param \App\Models\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail',
            'sms',
        ];
    }

    /**
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = "Новый заказ №{$this->order->number}";
        $body = "Поступил новый заказ №{$this->order->number}";

        return (new MailMessage)
            ->subject($subject)
            ->line($body)
            ->action('Перейти в заказы', url_frontend('/orders'));
    }

    /**
     * @param mixed $notifiable
     * @return string
     */
    public function toSms($notifiable)
    {
        return "Поступил новый заказ №{$this->order->number}";
    }
}
