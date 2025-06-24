<?php

namespace App\Http\Resources\Clients\API\Profile;

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
                    'address',
                    'phone',
                ]);
            }
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
            'store_phone' => $order->store->phone,

            'order_status_id' => $order->order_status_id,
            'order_delivery_type_id' => $order->order_delivery_type_id,
            'order_payment_type_id' => $order->order_payment_type_id,

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
        ];
    }
}
