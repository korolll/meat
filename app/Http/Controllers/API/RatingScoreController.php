<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\RatingScoreForAssortmentCollectionResponse;
use App\Models\Assortment;
use App\Models\Client;
use App\Models\RatingScore;

class RatingScoreController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function findForAssortmentsByClients()
    {
        $this->authorize('find-for-assortments-by-clients', RatingScore::class);

        return RatingScoreForAssortmentCollectionResponse::create(RatingScore::forAssortmentsByClients());
    }
}
