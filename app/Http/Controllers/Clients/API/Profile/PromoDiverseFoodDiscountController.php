<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Clients\API\Profile\PromoDiverseFoodClientDiscountResource;
use App\Http\Responses\Clients\API\Profile\PromoDiverseFoodClientDiscountResponse;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Services\Framework\Http\CollectionRequest;

class PromoDiverseFoodDiscountController extends Controller
{
    /**
     * @param \App\Services\Framework\Http\CollectionRequest $request
     *
     * @return \App\Http\Responses\Clients\API\Profile\PromoDiverseFoodClientDiscountResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(CollectionRequest $request)
    {
        $this->authorize('index-owned', PromoDiverseFoodClientDiscount::class);
        return new PromoDiverseFoodClientDiscountResponse(
            $request,
            $this->client->promoDiverseFoodClientDiscounts()
        );
    }

    /**
     * @param \App\Models\PromoDiverseFoodClientDiscount $discount
     *
     * @return \App\Http\Resources\Clients\API\Profile\PromoDiverseFoodClientDiscountResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(PromoDiverseFoodClientDiscount $discount)
    {
        $this->authorize('view', $discount);
        return PromoDiverseFoodClientDiscountResource::make($discount);
    }
}
