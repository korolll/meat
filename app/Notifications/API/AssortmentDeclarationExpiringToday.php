<?php

namespace App\Notifications\API;

use App\Models\Assortment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class AssortmentDeclarationExpiringToday extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var Assortment
     */
    public $assortment;

    /**
     * Create a new notification instance.
     * @param Assortment $assortment
     * @return void
     */
    public function __construct(Assortment $assortment)
    {
        $this->assortment = $assortment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = 'Срок декларации "' . $this->assortment->name . '" завершается сегодня';
        $body =
            'Срок действия декларации на номенклатуру "' . $this->assortment->name
            . '" завершается сегодня. Необходимо срочно обновить файл с декларацией.';

        return (new MailMessage)
            ->subject($subject)
            ->line($body);
    }
}
