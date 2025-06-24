<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class OrderCollectionResource extends JsonResource
{
    /**
     * @param \App\Models\Order $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'store' => function(Relation $query) {
                return $query->select([
                    'uuid',
                    'full_name',
                    'address'
                ]);
            },
            'client' => function (Relation $query) {
                return $query->select('*');
            },
            'orderStatus' => function (Relation $query) {
                return $query->select('*');
            },
            'orderPaymentType' => function (Relation $query) {
                return $query->select('*');
            },
            'orderDeliveryType' => function (Relation $query) {
                return $query->select('*');
            },
            'relatedClientPayments' => function (Relation $query) {
                return $query->select('*');
            },
        ]);
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return array
     */
    public function resource($order)
    {
        return [
            'uuid' => $order->uuid,
            'number' => $order->number,

            'store_user_uuid' => $order->store_user_uuid,
            'store_user_full_name' => $order->store->full_name,
            'store_user_address' => $order->store->address,

            'client_uuid' => $order->client_uuid,
            'client_credit_card_uuid' => $order->client_credit_card_uuid,
            'client_phone' => $order->client->phone,
            'client' => ClientResource::make($order->client),

            'order_status_id' => $order->order_status_id,
            'order_status' => OrderStatusResource::make($order->orderStatus),

            'order_delivery_type_id' => $order->order_delivery_type_id,
            'order_delivery_type' => OrderDeliveryTypeResource::make($order->orderDeliveryType),
            'courier_phone' => $order->courier_phone,

            'order_payment_type_id' => $order->order_payment_type_id,
            'order_payment_type' => OrderPaymentTypeResource::make($order->orderPaymentType),

            'client_comment' => $order->client_comment,
            'client_email' => $order->client_email,

            'client_address_data' => $order->client_address_data,

            'is_paid' => $order->is_paid,

            'total_price' => $order->total_price,
            'paid_bonus' => $order->paid_bonus,
            'bonus_to_charge' => $order->bonus_to_charge,

            'planned_delivery_datetime_from' => $order->planned_delivery_datetime_from,
            'planned_delivery_datetime_to' => $order->planned_delivery_datetime_to,

            'payments' => OrderClientPaymentResource::collection($order->relatedClientPayments),
        ];
    }
}
