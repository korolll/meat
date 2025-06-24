<?php

namespace App\Notifications\API;

use App\Models\ProductRequest;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class CustomerProductRequestStatusCanceled extends Notification implements ShouldQueue
{
    use HasStaticMakeMethod, SerializesModels, Queueable;

    /**
     * @var ProductRequest
     */
    public $productRequest;

    /**
     * @param ProductRequest $productRequest
     */
    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest;
    }

    /**
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $comment = '';
        if ($this->productRequest->customer_comment) {
            $comment = ' с комментарием: ' . $this->productRequest->customer_comment;
        }

        $customerOrganizationName = $this->productRequest->customerUser->organization_name;
        $createdAt = $this->productRequest->created_at->format('d.m.Y');

        return (new MailMessage())
            ->subject("Заявка от {$createdAt} отклонена по инициативе заказчика")
            ->line("Заявка от {$createdAt} отклонена по инициативе заказчика {$customerOrganizationName}{$comment}")
            ->line('Ссылка на заявку: ' . url_frontend('/shipment/' . $this->productRequest->uuid));
    }
}
