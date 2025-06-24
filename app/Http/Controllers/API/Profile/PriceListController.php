<?php

namespace App\Http\Controllers\API\Profile;

use App\Exceptions\ClientExceptions\FuturePriceListAlreadyExistsException;
use App\Exports\PriceListExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\PriceListStoreRequest;
use App\Http\Requests\PriceListUpdateRequest;
use App\Http\Resources\PriceListResource;
use App\Http\Responses\PriceListCollectionResponse;
use App\Models\PriceList;

class PriceListController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', PriceList::class);

        return PriceListCollectionResponse::create(
            $this->user->priceLists()
        );
    }

    /**
     * @param PriceListStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(PriceListStoreRequest $request)
    {
        $this->authorize('create', PriceList::class);

        $priceListExistsQuery = $this->user->priceLists()->future();

        if (is_null($request->customer_user_uuid)) {
            $priceListExistsQuery->whereNull('customer_user_uuid');
        } else {
            $priceListExistsQuery->where('customer_user_uuid', '=', $request->customer_user_uuid);
        }

        if ($priceListExistsQuery->exists()) {
            throw new FuturePriceListAlreadyExistsException();
        }

        $price_list = new PriceList($request->validated());
        $price_list->customer_user_uuid = $request->customer_user_uuid;
        $price_list->user()->associate($this->user);
        $price_list->saveOrFail();

        return PriceListResource::make($price_list);
    }

    /**
     * @param PriceList $priceList
     * @return PriceListResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(PriceList $priceList)
    {
        $this->authorize('view', $priceList);

        return PriceListResource::make($priceList);
    }

    /**
     * @param PriceListUpdateRequest $request
     * @param PriceList $priceList
     * @return PriceListResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(PriceListUpdateRequest $request, PriceList $priceList)
    {
        $this->authorize('update', $priceList);

        $priceList->fill($request->validated());
        $priceList->saveOrFail();

        return PriceListResource::make($priceList);
    }

    /**
     * @param PriceList $priceList
     * @return PriceListResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(PriceList $priceList)
    {
        $this->authorize('delete', $priceList);

        $priceList->delete();

        return PriceListResource::make($priceList);
    }

    /**
     * @param PriceList $priceList
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function export(PriceList $priceList)
    {
        $this->authorize('view', $priceList);

        return (new PriceListExport($priceList))->download('price_list.xlsx');
    }
}
