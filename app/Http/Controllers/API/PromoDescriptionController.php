<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoDescriptionStoreRequest;
use App\Http\Resources\PromoDescriptionResource;
use App\Http\Responses\PromoDescriptionCollectionResponse;
use App\Models\PromoDescription;

class PromoDescriptionController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', PromoDescription::class);

        return PromoDescriptionCollectionResponse::create(
            PromoDescription::query()
        );
    }

    /**
     * @param PromoDescriptionStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(PromoDescriptionStoreRequest $request)
    {
        $this->authorize('create', PromoDescription::class);

        $promoDescription = new PromoDescription($request->validated());
        $promoDescription->saveOrFail();

        return PromoDescriptionResource::make($promoDescription);
    }

    /**
     * @param PromoDescription $promoDescription
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(PromoDescription $promoDescription)
    {
        $this->authorize('view', $promoDescription);

        return PromoDescriptionResource::make($promoDescription);
    }

    /**
     * @param PromoDescriptionStoreRequest $request
     * @param PromoDescription $promoDescription
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(PromoDescriptionStoreRequest $request, PromoDescription $promoDescription)
    {
        $this->authorize('update', $promoDescription);

        $promoDescription->fill($request->validated());
        $promoDescription->saveOrFail();

        return PromoDescriptionResource::make($promoDescription);
    }

    /**
     * @param PromoDescription $promoDescription
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(PromoDescription $promoDescription)
    {
        $this->authorize('delete', $promoDescription);
        $promoDescription->delete();

        return PromoDescriptionResource::make($promoDescription);
    }
    
}
