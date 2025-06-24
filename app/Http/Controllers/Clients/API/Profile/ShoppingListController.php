<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Contracts\Models\ClientShoppingList\CreateClientShoppingListContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\ShoppingListStoreRequest;
use App\Http\Resources\Clients\API\Profile\ShoppingListResource;
use App\Http\Responses\Clients\API\Profile\ShoppingListResponse;
use App\Models\ClientShoppingList;
use App\Models\User;
use App\Services\Models\Assortment\Discount\AssortmentDiscountApplierInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ShoppingListController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('index', ClientShoppingList::class);

        $call = null;
        $storeUuid = $request->get('store_uuid');
        if (is_scalar($storeUuid) && Uuid::isValid((string)$storeUuid)) {
            $store = User::findOrFail($storeUuid);
            $call = function (LengthAwarePaginator $paginator) use ($store) {
                $assortmentsMap = [];
                $lists = new Collection();
                foreach ($paginator as $item) {
                    $lists[] = $item;
                }
                ShoppingListResource::loadMissing($lists);

                /** @var \App\Models\ClientShoppingList $item */
                foreach ($lists as $item) {
                    foreach ($item->assortments as $assortment) {
                        $assortmentsMap[$assortment->uuid][] = $assortment;
                    }
                }

                /** @var AssortmentDiscountApplierInterface $applier */
                $applier = app(AssortmentDiscountApplierInterface::class);
                $applier->apply($store, $this->client, $assortmentsMap, false, true);
            };
        }

        $result = ShoppingListResponse::create($this->client->shoppingLists());
        if ($call) {
            $result->setBeforeToResource($call);
        }

        return $result;
    }

    /**
     * @param ShoppingListStoreRequest $request
     * @param CreateClientShoppingListContract $shoppingListFactory
     * @return ShoppingListResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ShoppingListStoreRequest $request, CreateClientShoppingListContract $shoppingListFactory)
    {
        $this->authorize('create', ClientShoppingList::class);

        $customerList = $shoppingListFactory->create($this->client, $request->get('name'), $request->assortments());

        return ShoppingListResource::make($customerList);
    }

    /**
     * @param ClientShoppingList       $shoppingList
     * @param \Illuminate\Http\Request $request
     *
     * @return ShoppingListResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ClientShoppingList $shoppingList, Request $request)
    {
        $this->authorize('show', $shoppingList);

        $storeUuid = $request->get('store_uuid');
        if (is_scalar($storeUuid) && Uuid::isValid((string)$storeUuid)) {
            $store = User::findOrFail($storeUuid);
            ShoppingListResource::loadMissing($shoppingList);
            $assortmentsMap = [];
            foreach ($shoppingList->assortments as $assortment) {
                $assortmentsMap[$assortment->uuid] = $assortment;
            }

            /** @var AssortmentDiscountApplierInterface $applier */
            $applier = app(AssortmentDiscountApplierInterface::class);
            $applier->apply($store, $this->client, $assortmentsMap, false, true);
        }

        return ShoppingListResource::make($shoppingList);
    }

    /**
     * @param ShoppingListStoreRequest $request
     * @param ClientShoppingList $shoppingList
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(ShoppingListStoreRequest $request, ClientShoppingList $shoppingList)
    {
        $this->authorize('update', $shoppingList);

        $shoppingList->fill($request->validated());

        DB::transaction(function () use ($shoppingList, $request) {
            $shoppingList->save();
            if ($request->has('assortments')) {
                $shoppingList->assortments()->sync($request->assortments());
            }
        });

        return ShoppingListResource::make($shoppingList);
    }

    /**
     * @param ClientShoppingList $shoppingList
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(ClientShoppingList $shoppingList)
    {
        $this->authorize('delete', $shoppingList);

        $shoppingList->delete();

        return ShoppingListResource::make($shoppingList);
    }
}
