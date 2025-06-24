<?php

namespace App\Notifications\API;

use App\Models\ProductRequest;
use App\Models\ProductRequestSupplierStatus;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class SupplierProductRequestStatusDoneOrRefused extends Notification implements ShouldQueue
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
        $state = $this->productRequest->product_request_supplier_status_id === ProductRequestSupplierStatus::ID_DONE ? 'подтверждена' : 'отклонена';

        $comment = '';
        if ($this->productRequest->supplier_comment) {
            $comment = ' с комментарием: ' . $this->productRequest->supplier_comment;
        }

        $supplierOrganizationName = $this->productRequest->supplierUser->organization_name;
        $createdAt = $this->productRequest->created_at->format('d.m.Y');

        return (new MailMessage())
            ->subject("Заявка от {$createdAt} {$state} поставщиком")
            ->line("Заявка от {$createdAt} {$state} поставщиком {$supplierOrganizationName}{$comment}")
            ->line('Ссылка на заявку: ' . url_frontend('/shipment/' . $this->productRequest->uuid));
    }
}
