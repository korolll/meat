<?php

namespace App\Http\Controllers\API\ProductRequests;

use App\Http\Controllers\Controller;
use App\Http\Responses\DeliveryProductRequestCollectionResponse;
use App\Models\ProductRequest;
use App\Models\ProductRequests\DeliveryProductRequest;

class DeliveryProductRequestController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-monitoring', DeliveryProductRequest::class);

        return DeliveryProductRequestCollectionResponse::create(
            ProductRequest::waitingForDelivery()
        );
    }
}
