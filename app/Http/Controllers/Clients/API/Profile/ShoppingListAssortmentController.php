<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\ShoppingListAssortmentAttachRequest;
use App\Models\Assortment;
use App\Models\ClientShoppingList;
use Symfony\Component\HttpFoundation\Response;

class ShoppingListAssortmentController extends Controller
{
    /**
     * @param \App\Http\Requests\Clients\API\Profile\ShoppingListAssortmentAttachRequest $request
     * @param \App\Models\ClientShoppingList                                             $shoppingList
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ShoppingListAssortmentAttachRequest $request, ClientShoppingList $shoppingList)
    {
        $this->authorize('attach-assortment', $shoppingList);

        $assortment = Assortment::findOrFail($request->get('assortment_uuid'));

        $shoppingList->assortments()->syncWithoutDetaching([
            $assortment->uuid => ['quantity' => $request->get('quantity')]
        ]);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Models\ClientShoppingList $shoppingList
     * @param \App\Models\Assortment         $assortment
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(ClientShoppingList $shoppingList, Assortment $assortment)
    {
        $this->authorize('detach-assortment', $shoppingList);

        $count = $shoppingList->assortments()->detach($assortment);

        return response('', $count ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }
}
