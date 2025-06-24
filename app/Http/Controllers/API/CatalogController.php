<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ClientExceptions\CatalogNotEmptyException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogStoreRequest;
use App\Http\Resources\AssortmentResourceCollection;
use App\Http\Resources\CatalogResource;
use App\Http\Responses\CatalogCollectionResponse;
use App\Models\Catalog;
use App\Services\Storaging\Catalog\Contracts\CatalogRemoverContract;
use Illuminate\Http\Response;

class CatalogController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Catalog::class);

        return CatalogCollectionResponse::create(
            Catalog::public()
        );
    }

    /**
     * @param CatalogStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CatalogStoreRequest $request)
    {
        $this->authorize('create', Catalog::class);

        $catalog = new Catalog($request->validated());
        $catalog->saveOrFail();

        return CatalogResource::make($catalog);
    }

    /**
     * @param Catalog $catalog
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Catalog $catalog)
    {
        $this->authorize('view', $catalog);

        return CatalogResource::make($catalog);
    }

    /**
     * @param CatalogStoreRequest $request
     * @param Catalog $catalog
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(CatalogStoreRequest $request, Catalog $catalog)
    {
        $this->authorize('update', $catalog);

        $catalog->fill($request->validated());
        $catalog->saveOrFail();

        return CatalogResource::make($catalog);
    }

    /**
     * @param Catalog $catalog
     * @param CatalogRemoverContract $remover
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Catalog $catalog, CatalogRemoverContract $remover)
    {
        $this->authorize('delete', $catalog);

        try {
            $remover->remove($catalog);
        } catch (CatalogNotEmptyException $e) {
            return AssortmentResourceCollection::make($e->getAssortments()->toEloquent())
                ->response()
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return CatalogResource::make($catalog);
    }
}
