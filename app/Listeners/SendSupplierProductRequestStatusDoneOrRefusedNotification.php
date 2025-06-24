<?php

namespace App\Listeners;

use App\Events\SupplierProductRequestStatusChanged;
use App\Models\ProductRequestSupplierStatus;
use App\Notifications\API\SupplierProductRequestStatusDoneOrRefused;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSupplierProductRequestStatusDoneOrRefusedNotification implements ShouldQueue
{
    /**
     * @param SupplierProductRequestStatusChanged $event
     */
    public function handle(SupplierProductRequestStatusChanged $event)
    {
        $status = $event->productRequest->product_request_supplier_status_id;
        if ($status === ProductRequestSupplierStatus::ID_DONE || $status === ProductRequestSupplierStatus::ID_SUPPLIER_REFUSED) {
            $notification = SupplierProductRequestStatusDoneOrRefused::make($event->productRequest);
            $event->productRequest->customerUser->notify($notification);
        }
    }
}