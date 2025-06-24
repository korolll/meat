<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Http\Resources\OrderProductResourceCollection;
use App\Models\Order;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use App\Services\Management\Client\Order\System\SystemOrderSettingStorageInterface;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use App\Services\Quantity\FloatHelper;

class OrderResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'store' => function(Relation $query) {
                return $query->select([
                    'uuid',
                    'full_name',
                    'address',
                    'work_hours_from',
                    'work_hours_till',
                    'phone',
                ]);
            },
            'orderProducts'
        ]);

        OrderProductResourceCollection::loadMissing($resource, 'orderProducts.');
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return array
     */
    public function resource($order)
    {
        /** @var SystemOrderSettingStorageInterface $storage */
        $storage = app(SystemOrderSettingStorageInterface::class);
        $freeThreshold = $storage->getDeliveryThreshold();

        $diff = $freeThreshold - $order->total_price_for_products_with_discount;
        if ($order->delivery_price != 0 && $diff > 0) {
            $toFreeDelivery = $diff;
        } else {
            $toFreeDelivery = 0;
        }

        $minPrice = $storage->getMinPrice();
        $toMinPrice = null;
        if ($minPrice > 0) {
            $diff = $minPrice - $order->total_price_for_products_with_discount;
            if ($diff > 0) {
                $toMinPrice = $diff;
            } else {
                $toMinPrice = 0;
            }
        }

        return [
            'uuid' => $order->uuid,
            'number' => $order->number,

            'store_user_uuid' => $order->store_user_uuid,
            'store_user_full_name' => $order->store->full_name,
            'store_user_address' => $order->store->address,
            'work_hours_from' => $order->store->work_hours_from,
            'work_hours_till' => $order->store->work_hours_till,
            'store_phone' => $order->store->phone,

            'order_status_id' => $order->order_status_id,
            'order_delivery_type_id' => $order->order_delivery_type_id,
            'order_payment_type_id' => $order->order_payment_type_id,

            'client_comment' => $order->client_comment,
            'client_email' => $order->client_email,

            'client_address_data' => $order->client_address_data,
            'is_paid' => $order->is_paid,

            'delivery_price' => $order->delivery_price,
            'to_free_delivery' => FloatHelper::round($toFreeDelivery),
            'to_min_price' => $toMinPrice !== null ? FloatHelper::round($toMinPrice) : null,
            'min_price' => $minPrice,

            'total_discount_for_products' => $order->total_discount_for_products,
            'total_price_for_products_with_discount' => $order->total_price_for_products_with_discount,

            'total_price' => $order->total_price,
            'total_weight' => $order->total_weight,
            'total_quantity' => $order->total_quantity,

            'total_bonus' => $order->total_bonus,
            'paid_bonus' => $order->paid_bonus,
            'bonus_to_charge' => $order->bonus_to_charge,
            'max_bonus_to_paid' => $order->getVirtualValue(Order::VIRTUAL_ATTR_MAX_BONUS),

            'planned_delivery_datetime_from' => $order->planned_delivery_datetime_from,
            'planned_delivery_datetime_to' => $order->planned_delivery_datetime_to,

            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,

            'products' => OrderProductResourceCollection::collection($order->orderProducts),

            'promocode' => $order->promocode,
        ];
    }
}
