<?php

namespace App\Listeners;

use App\Events\OrderIsCreated;
use App\Models\OrderNotificationSetting;
use App\Models\OrderStatus;
use App\Notifications\Clients\API\OrderStatusNotificationBySetting;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendClientOrderNotification implements ShouldQueue
{
    /**
     * @param \App\Events\OrderStatusChanged|\App\Events\OrderIsCreated $event
     */
    public function handle($event)
    {
        $order = $event->order;
        $client = $order->client;

        if ($event instanceof OrderIsCreated) {
            $orderStatusId = OrderStatus::ID_NEW;
        } else {
            $orderStatusId = $event->newStatusId;
        }

        $notification = $this->resolveNotification($orderStatusId, $order->order_delivery_type_id);
        if (!$notification) {
            return;
        }

        $status = OrderStatus::find($orderStatusId);
        $client->notify(OrderStatusNotificationBySetting::make($order, $notification, $status));
    }

    protected function resolveNotification(string $orderStatusId, string $orderDeliveryTypeId): ?OrderNotificationSetting
    {
        return OrderNotificationSetting::query()
            ->where('order_status_id', $orderStatusId)
            ->where('order_delivery_type_id', $orderDeliveryTypeId)
            ->first();
    }
}
