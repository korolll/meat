<?php

namespace App\Listeners;

use App\Events\CustomerProductRequestStatusChanged;
use App\Models\ProductRequestCustomerStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\API\CustomerProductRequestStatusOnMatching;

class SendCustomerProductRequestStatusOnMatchingNotification implements ShouldQueue
{
    /**
     * @param CustomerProductRequestStatusChanged $event
     */
    public function handle(CustomerProductRequestStatusChanged $event)
    {
        if ($event->productRequest->product_request_customer_status_id === ProductRequestCustomerStatus::ID_MATCHING) {
            $notification = CustomerProductRequestStatusOnMatching::make($event->productRequest);
            $event->productRequest->customerUser->notify($notification);
        }
    }
}