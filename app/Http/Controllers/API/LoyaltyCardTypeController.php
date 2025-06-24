<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoyaltyCardTypeStoreRequest;
use App\Http\Resources\LoyaltyCardTypeResource;
use App\Http\Responses\LoyaltyCardTypeCollectionResponse;
use App\Models\LoyaltyCardType;

class LoyaltyCardTypeController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', LoyaltyCardType::class);

        return LoyaltyCardTypeCollectionResponse::create(
            LoyaltyCardType::query()
        );
    }

    /**
     * @param LoyaltyCardTypeStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(LoyaltyCardTypeStoreRequest $request)
    {
        $this->authorize('create', LoyaltyCardType::class);

        $loyaltyCardType = new LoyaltyCardType($request->validated());
        $loyaltyCardType->saveOrFail();

        return LoyaltyCardTypeResource::make($loyaltyCardType);
    }

    /**
     * @param LoyaltyCardType $loyaltyCardType
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(LoyaltyCardType $loyaltyCardType)
    {
        $this->authorize('view', $loyaltyCardType);

        return LoyaltyCardTypeResource::make($loyaltyCardType);
    }

    /**
     * @param LoyaltyCardTypeStoreRequest $request
     * @param LoyaltyCardType $loyaltyCardType
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(LoyaltyCardTypeStoreRequest $request, LoyaltyCardType $loyaltyCardType)
    {
        $this->authorize('update', $loyaltyCardType);

        $loyaltyCardType->fill($request->validated());
        $loyaltyCardType->saveOrFail();

        return LoyaltyCardTypeResource::make($loyaltyCardType);
    }
}
