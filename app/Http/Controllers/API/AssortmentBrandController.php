<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ClientExceptions\AssortmentBrandAssociatedWithAssortmentsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssortmentBrandStoreRequest;
use App\Http\Resources\AssortmentBrandResource;
use App\Http\Responses\AssortmentBrandCollectionResponse;
use App\Models\AssortmentBrand;

class AssortmentBrandController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', AssortmentBrand::class);

        return AssortmentBrandCollectionResponse::create(
            AssortmentBrand::query()
        );
    }

    /**
     * @param AssortmentBrandStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(AssortmentBrandStoreRequest $request)
    {
        $this->authorize('create', AssortmentBrand::class);

        $assortmentBrand = AssortmentBrand::firstOrCreate([
            'name' => $request->name,
        ]);

        return AssortmentBrandResource::make($assortmentBrand);
    }

    /**
     * @param AssortmentBrand $assortmentBrand
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(AssortmentBrand $assortmentBrand)
    {
        $this->authorize('view', $assortmentBrand);

        return AssortmentBrandResource::make($assortmentBrand);
    }

    /**
     * @param AssortmentBrandStoreRequest $request
     * @param AssortmentBrand $assortmentBrand
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(AssortmentBrandStoreRequest $request, AssortmentBrand $assortmentBrand)
    {
        $this->authorize('update', $assortmentBrand);

        $assortmentBrand->fill($request->validated());
        $assortmentBrand->saveOrFail();

        return AssortmentBrandResource::make($assortmentBrand);
    }

    /**
     * @param AssortmentBrand $assortmentBrand
     * @return AssortmentBrandResource
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(AssortmentBrand $assortmentBrand)
    {
        $this->authorize('delete', $assortmentBrand);

        if ($assortmentBrand->assortments()->exists()) {
            throw new AssortmentBrandAssociatedWithAssortmentsException();
        }

        $assortmentBrand->delete();

        return AssortmentBrandResource::make($assortmentBrand);
    }
}
