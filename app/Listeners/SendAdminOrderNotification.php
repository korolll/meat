<?php

namespace App\Listeners;

use App\Events\OrderIsCreated;
use App\Models\OrderStatus;
use App\Notifications\API\OrderIsCreated as OrderIsCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendAdminOrderNotification implements ShouldQueue
{
    /**
     * @param \App\Events\OrderStatusChanged|\App\Events\OrderIsCreated $event
     */
    public function handle($event)
    {
        $order = $event->order;
        $store = $order->store;
        $toAdminPhones = config('app.sms.admin_phones') ?: [];
        $toStoreAdminPhones = $store->admin_phones ? explode(',', $store->admin_phones) : [];
        $to = array_unique(
            array_merge($toStoreAdminPhones, $toAdminPhones)
        );
        if ($event instanceof OrderIsCreated) {
            Notification::route('sms', $to)->notify(new OrderIsCreatedNotification($order));
        }
    }
}