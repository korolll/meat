<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoyaltyCardStoreRequest;
use App\Http\Requests\LoyaltyCardUpdateRequest;
use App\Http\Resources\LoyaltyCardResource;
use App\Http\Responses\LoyaltyCardCollectionResponse;
use App\Models\LoyaltyCard;

class LoyaltyCardController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', LoyaltyCard::class);

        return LoyaltyCardCollectionResponse::create(
            LoyaltyCard::query()
        );
    }

    /**
     * @param LoyaltyCardStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(LoyaltyCardStoreRequest $request)
    {
        $this->authorize('create', LoyaltyCard::class);

        $loyaltyCard = new LoyaltyCard($request->validated());
        $loyaltyCard->saveOrFail();

        return LoyaltyCardResource::make($loyaltyCard);
    }

    /**
     * @param LoyaltyCard $loyaltyCard
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(LoyaltyCard $loyaltyCard)
    {
        $this->authorize('view', $loyaltyCard);

        return LoyaltyCardResource::make($loyaltyCard);
    }

    /**
     * @param LoyaltyCardUpdateRequest $request
     * @param LoyaltyCard $loyaltyCard
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(LoyaltyCardUpdateRequest $request, LoyaltyCard $loyaltyCard)
    {
        $this->authorize('update', $loyaltyCard);

        $loyaltyCard->fill($request->validated());
        $loyaltyCard->saveOrFail();

        return LoyaltyCardResource::make($loyaltyCard);
    }
}
