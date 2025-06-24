<?php

namespace App\Http\Resources;

class OrderResourceWithRating extends OrderResource
{
    /**
     * @param \App\Models\Order $order
     *
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     */
    protected function makeProducts($order)
    {
        return OrderProductResourceWithRating::collection($order->orderProducts);
    }

    /**
     * @param $resource
     *
     * @return void
     */
    protected static function loadMissingProduct($resource)
    {
        OrderProductResourceWithRating::loadMissing($resource, 'orderProducts.');
    }
}
