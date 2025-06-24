<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\ShoppingCartBulkStoreRequest;
use App\Http\Requests\Clients\API\Profile\ShoppingCartStoreRequest;
use App\Http\Resources\Clients\API\Profile\CartAssortmentResource;
use App\Models\ClientShoppingList;
use App\Models\Order;
use App\Models\User;
use App\Services\Models\Assortment\Discount\AssortmentDiscountApplierInterface;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShoppingCartController extends Controller
{
    /**
     *
     */
    public function index(Request $request)
    {
        $cart = $this->client->getShoppingCart();

        $assortments = $cart->getAssortmentList();
        $storeUuid = $request->get('store_uuid');
        if (is_scalar($storeUuid) && Uuid::isValid((string)$storeUuid)) {
            $store = User::findOrFail($storeUuid);
            $assortmentsMap = [];
            /** @var \App\Models\Assortment $assortment */
            foreach ($assortments as $assortment) {
                $assortmentsMap[$assortment->uuid] = $assortment;
            }

            /** @var AssortmentDiscountApplierInterface $applier */
            $applier = app(AssortmentDiscountApplierInterface::class);
            $applier->apply($store, $this->client, $assortmentsMap, true);
            $assortments = array_values($assortmentsMap);
        }

        return CartAssortmentResource::collection($assortments);
    }

    /**
     * @param \App\Http\Requests\Clients\API\Profile\ShoppingCartStoreRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(ShoppingCartStoreRequest $request)
    {
        $cart = $this->client->getShoppingCart();
        $cart->add($request->get('uuid'), $request->get('quantity'));
        $cart->save();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Http\Requests\Clients\API\Profile\ShoppingCartStoreRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function bulkStore(ShoppingCartBulkStoreRequest $request)
    {
        $cart = $this->client->getShoppingCart();

        foreach ($request->input('items') as $item) {
            if (!$item['quantity']) {
                $cart->delete($item['uuid']);
            } else {
                $cart->update($item['uuid'], $item['quantity']);
            }
        }

        $cart->save();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $assortmentUuid
     *
     * @return \App\Http\Resources\Clients\API\Profile\CartAssortmentResource
     */
    public function show(string $assortmentUuid)
    {
        $cart = $this->client->getShoppingCart();
        $assortment = $cart->get($assortmentUuid);
        if (! $assortment) {
            throw new NotFoundHttpException();
        }

        return CartAssortmentResource::make($assortment);
    }

    /**
     * @param \App\Http\Requests\Clients\API\Profile\ShoppingCartStoreRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function customUpdate(ShoppingCartStoreRequest $request)
    {
        $cart = $this->client->getShoppingCart();
        $cart->update($request->get('uuid'), $request->get('quantity'));
        $cart->save();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $assortmentUuid
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function destroy(string $assortmentUuid)
    {
        $cart = $this->client->getShoppingCart();
        $cart->delete($assortmentUuid);
        $cart->save();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function clear()
    {
        $cart = $this->client->getShoppingCart();
        $cart->clear();
        $cart->save();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Models\ClientShoppingList $shoppingList
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function fillFromShoppingList(ClientShoppingList $shoppingList)
    {
        $this->authorize('show', $shoppingList);

        $cart = $this->client->getShoppingCart();
        foreach ($shoppingList->assortments as $assortment) {
            $cart->update($assortment->uuid, $assortment->pivot->quantity ?: 0);
        }

        $cart->save();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function fillFromOrder(Order $order)
    {
        $this->authorize('view', $order);

        $cart = $this->client->getShoppingCart();
        $orderProducts = $order->orderProducts()->with('product')->get();
        /** @var \App\Models\OrderProduct $orderProduct */
        foreach ($orderProducts as $orderProduct) {
            $cart->update($orderProduct->product->assortment_uuid, $orderProduct->quantity);
        }

        $cart->save();
        return response('', Response::HTTP_NO_CONTENT);
    }
}
