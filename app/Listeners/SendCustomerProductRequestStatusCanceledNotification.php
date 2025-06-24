<?php

namespace App\Listeners;

use App\Events\CustomerProductRequestStatusChanged;
use App\Models\ProductRequestCustomerStatus;
use App\Notifications\API\CustomerProductRequestStatusCanceled;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCustomerProductRequestStatusCanceledNotification implements ShouldQueue
{
    /**
     * @param CustomerProductRequestStatusChanged $event
     */
    public function handle(CustomerProductRequestStatusChanged $event)
    {
        if ($event->productRequest->product_request_customer_status_id === ProductRequestCustomerStatus::ID_USER_CANCELED) {
            $notification = CustomerProductRequestStatusCanceled::make($event->productRequest);
            $event->productRequest->supplierUser->notify($notification);
        }
    }
}