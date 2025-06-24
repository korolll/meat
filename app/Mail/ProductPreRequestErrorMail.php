<?php

namespace App\Mail;

use App\Models\ProductPreRequest;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProductPreRequestErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var ProductPreRequest[]|Collection
     */
    protected $badProductPreRequests;

    /**
     * Create a new message instance.
     *
     * @param ProductPreRequest[]|Collection $badProductPreRequests
     * @throws Exception
     */
    public function __construct($badProductPreRequests)
    {
        if ($badProductPreRequests instanceof Collection && $badProductPreRequests->count() === 0) {
            throw new Exception('Bad ProductPreRequests count must be over nil');
        }
        $this->badProductPreRequests = $badProductPreRequests;
    }

    protected function prepareMessages()
    {
        $message = [];
        foreach ($this->badProductPreRequests->groupBy('product.user.organization_name') as $groupOrganizationName => $groupArray) {
            $messageData = ['groupOrganizationName' => $groupOrganizationName];

            foreach ($groupArray as $productPreRequest) {
                $messageData['productPreRequests'][] = [
                    'productPreRequestId' => $productPreRequest->id,
                    'assortmentName' => $productPreRequest->product->assortment->name,
                    'supplierName' => $productPreRequest->product->user->organization_name,
                    'productRequestUuid' => $productPreRequest->product_request_uuid,
                    'customerName' => $productPreRequest->user->organization_name,
                    'dateCustomerRequest' => $productPreRequest->productRequest->expected_delivery_date->toDateTimeString(),
                ];
            }
            $message[] = $messageData;
        }

        return $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->to(config('services.notifications.product_pre_request_error_emails'))
            ->markdown('emails.product_pre_requests.error_on_create_supplier_request', [
                'messages' => $this->prepareMessages(),
            ]);
    }
}
