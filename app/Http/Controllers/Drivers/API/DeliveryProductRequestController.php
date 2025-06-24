<?php

namespace App\Http\Controllers\Drivers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryProductRequestResource;
use App\Http\Responses\ProductRequestProductCollectionResponse;
use App\Models\ProductRequests\DeliveryProductRequest;

class DeliveryProductRequestController extends Controller
{
    /**
     * @param DeliveryProductRequest $productRequest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(DeliveryProductRequest $productRequest)
    {
        $this->authorize('view', $productRequest);

        return DeliveryProductRequestResource::make($productRequest);
    }

    /**
     * @param DeliveryProductRequest $productRequest
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function products(DeliveryProductRequest $productRequest)
    {
        $this->authorize('view', $productRequest);

        return ProductRequestProductCollectionResponse::create($productRequest->products());
    }
}
