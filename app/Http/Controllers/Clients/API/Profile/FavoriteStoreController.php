<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\FavoriteStoreStoreRequest;
use App\Models\User;
use Illuminate\Http\Response;

class FavoriteStoreController extends Controller
{
    /**
     * @param FavoriteStoreStoreRequest $request
     * @return mixed
     */
    public function store(FavoriteStoreStoreRequest $request)
    {
        $this->client->favoriteStores()->syncWithoutDetaching($request->store_uuid);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param User $favoriteStore
     * @return mixed
     */
    public function destroy(User $favoriteStore)
    {
        $count = $this->client->favoriteStores()->detach($favoriteStore);

        return response('', $count ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }
}
