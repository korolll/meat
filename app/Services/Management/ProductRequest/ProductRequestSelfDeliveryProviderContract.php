<?php

namespace App\Services\Management\ProductRequest;

use App\Models\ProductRequest;

interface ProductRequestSelfDeliveryProviderContract
{
    /**
     * @param ProductRequest $productRequest
     * @return ProductRequest
     */
    public function provide(ProductRequest $productRequest): ProductRequest;
}