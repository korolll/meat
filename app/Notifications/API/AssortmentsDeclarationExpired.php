<?php

namespace App\Notifications\API;

use App\Models\Assortment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class AssortmentsDeclarationExpired extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var Assortment[]|Collection
     */
    public $assortments;

    /**
     * AssortmentDeclarationExpired constructor.
     * @param Assortment[]|Collection $assortments
     */
    public function __construct(Collection $assortments)
    {
        $this->assortments = $assortments;
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
        $mail = (new MailMessage)
            ->subject('Список номенклатур с истекшим сроком деклараций')
            ->line('Срок действия деклараций истек на следующие номенклатуры: ');

        foreach ($this->assortments as $assortment) {
            $mail->line(implode('; ', [
                $assortment->barcodes->toJson(),
                $assortment->name,
                $assortment->declaration_end_date->format('d.m.Y'),
            ]));
        }

        return $mail
            ->line('Необходимо срочно обновить файлы с декларациями для данных номенклатур.');
    }
}
