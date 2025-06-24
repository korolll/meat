<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\FavoriteAssortmentStoreRequest;
use App\Models\Assortment;
use App\Models\Client;
use Symfony\Component\HttpFoundation\Response;

class FavoriteAssortmentController extends Controller
{
    /**
     * @param FavoriteAssortmentStoreRequest $request
     *
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(FavoriteAssortmentStoreRequest $request)
    {
        $assortment = Assortment::findOrFail($request->assortment_uuid);

        $this->authorize('favorite-assortment-attach', Client::class);

        $this->client->favoriteAssortments()->syncWithoutDetaching($assortment);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Assortment $assortment
     *
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Assortment $assortment)
    {
        $this->authorize('favorite-assortment-detach', Client::class);

        $count = $this->client->favoriteAssortments()->detach($assortment);

        return response('', $count ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }
}
