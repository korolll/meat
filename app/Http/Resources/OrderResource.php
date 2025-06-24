<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class OrderResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'orderProducts' => function (Relation $query) {
                return $query->select('*');
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
            'store' => function (Relation $query) {
                return $query->select('uuid', 'address', 'full_name');
            },
        ]);

        static::loadMissingProduct($resource);
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
            'client' => ClientResource::make($order->client),

            'order_status_id' => $order->order_status_id,
            'order_status' => OrderStatusResource::make($order->orderStatus),

            'order_delivery_type_id' => $order->order_delivery_type_id,
            'order_delivery_type' => OrderDeliveryTypeResource::make($order->orderDeliverType),

            'order_payment_type_id' => $order->order_payment_type_id,
            'order_payment_type' => OrderPaymentTypeResource::make($order->orderPaymentType),

            'client_comment' => $order->client_comment,
            'client_email' => $order->client_email,

            'client_address_data' => $order->client_address_data,

            'is_paid' => $order->is_paid,

            'delivery_price' => $order->delivery_price,
            'total_discount_for_products' => $order->total_discount_for_products,
            'total_price_for_products_with_discount' => $order->total_price_for_products_with_discount,
            'courier_phone' => $order->courier_phone,

            'total_price' => $order->total_price,
            'total_weight' => $order->total_weight,
            'total_quantity' => $order->total_quantity,

            'total_bonus' => $order->total_bonus,
            'paid_bonus' => $order->paid_bonus,
            'bonus_to_charge' => $order->bonus_to_charge,

            'planned_delivery_datetime_from' => $order->planned_delivery_datetime_from,
            'planned_delivery_datetime_to' => $order->planned_delivery_datetime_to,

            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,

            'products' => $this->makeProducts($order),
            'payments' => OrderClientPaymentResource::collection($order->relatedClientPayments),
        ];
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     */
    protected function makeProducts($order)
    {
        return OrderProductResource::collection($order->orderProducts);
    }

    /**
     * @param $resource
     *
     * @return void
     */
    protected static function loadMissingProduct($resource)
    {
        $resource->orderProducts = $resource->orderProducts->sortBy(function ($orderProduct) {
            return $orderProduct->product->assortment->catalog_uuid;
        })->values();

        OrderProductResource::loadMissing($resource, 'orderProducts.');
    }
}
