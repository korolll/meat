<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Responses\Clients\API\Profile\ClientActivePromoFavoriteAssortmentResponse;
use App\Services\Framework\Http\CollectionRequest;

class ActivePromoFavoriteAssortmentController extends Controller
{
    /**
     * @param \App\Services\Framework\Http\CollectionRequest $request
     *
     * @return \App\Http\Responses\Clients\API\Profile\ClientActivePromoFavoriteAssortmentResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function index(CollectionRequest $request)
    {
        $client = $this->client;
        return new ClientActivePromoFavoriteAssortmentResponse(
            $request,
            $client->clientActivePromoFavoriteAssortments()
        );
    }
}
