<?php

namespace App\Services\Management\ProductRequest;

use App\Models\ProductRequest;
use App\Models\ProductRequestDeliveryStatus;

class ProductRequestSelfDeliveryProvider implements ProductRequestSelfDeliveryProviderContract
{
    /**
     * @param ProductRequest $productRequest
     * @return ProductRequest
     * @throws \App\Exceptions\TealsyException
     */
    public function provide(ProductRequest $productRequest): ProductRequest
    {
        app(StatusTransitionManagerContract::class, compact('productRequest'))
            ->transition('product_request_delivery_status_id', ProductRequestDeliveryStatus::ID_IN_WORK);

        $productRequest->delivery_user_uuid = $productRequest->customer_user_uuid;

        return $productRequest;
    }
}