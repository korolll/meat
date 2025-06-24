<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\FavoriteMealReceiptStoreRequest;
use App\Models\MealReceipt;
use Illuminate\Http\Response;

class FavoriteMealReceiptController extends Controller
{
    /**
     * @param FavoriteMealReceiptStoreRequest $request
     * @return mixed
     */
    public function store(FavoriteMealReceiptStoreRequest $request)
    {
        $this->client->favoriteMealReceipts()->syncWithoutDetaching($request->meal_receipt_uuid);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param MealReceipt $favoriteMealReceipt
     * @return mixed
     */
    public function destroy(MealReceipt $favoriteMealReceipt)
    {
        $count = $this->client->favoriteMealReceipts()->detach($favoriteMealReceipt);

        return response('', $count ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }
}
