<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
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
        $this->authorize('index-owned', LoyaltyCardType::class);

        return LoyaltyCardTypeCollectionResponse::create(
            $this->user->loyaltyCardTypes()
        );
    }
}
