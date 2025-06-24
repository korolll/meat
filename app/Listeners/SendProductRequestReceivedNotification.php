<?php

namespace App\Listeners;

use App\Notifications\API\ProductRequestReceived;

/**
 * Class SendProductRequestReceivedNotification
 *
 * @package App\Listeners
 */
class SendProductRequestReceivedNotification
{
    /**
     * @param \App\Events\ProductRequestCreated|\App\Events\ProductRequestStatusChanged $event
     */
    public function handle($event)
    {
        if ($event->isSendMail){
            $notification = ProductRequestReceived::make($event->productRequest);
            $event->productRequest->supplierUser->notify($notification);
        }
    }
}
