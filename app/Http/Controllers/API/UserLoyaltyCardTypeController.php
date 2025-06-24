<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoyaltyCardTypeStoreRequest;
use App\Http\Responses\LoyaltyCardTypeCollectionResponse;
use App\Models\LoyaltyCardType;
use App\Models\User;
use Illuminate\Http\Response;

class UserLoyaltyCardTypeController extends Controller
{
    /**
     * @param User $user
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(User $user)
    {
        $this->authorize('index-owned-by', [LoyaltyCardType::class, $user]);

        return LoyaltyCardTypeCollectionResponse::create(
            $user->loyaltyCardTypes()
        );
    }

    /**
     * @param UserLoyaltyCardTypeStoreRequest $request
     * @param User $user
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(UserLoyaltyCardTypeStoreRequest $request, User $user)
    {
        $loyaltyCardType = LoyaltyCardType::findOrFail($request->loyalty_card_type_uuid);

        $this->authorize('attach-to', [$loyaltyCardType, $user]);

        $user->loyaltyCardTypes()->syncWithoutDetaching($loyaltyCardType);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param User $user
     * @param LoyaltyCardType $loyaltyCardType
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(User $user, LoyaltyCardType $loyaltyCardType)
    {
        $this->authorize('detach-from', [$loyaltyCardType, $user]);

        $count = $user->loyaltyCardTypes()->detach($loyaltyCardType);

        return response('', $count ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }
}
