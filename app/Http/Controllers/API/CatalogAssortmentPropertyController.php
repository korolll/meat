<?php

namespace App\Http\Controllers\API;

use App\Contracts\Models\Catalog\AttachAssortmentPropertyToCatalogContract;
use App\Contracts\Models\Catalog\DetachAssortmentPropertyFromCatalogContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogAssortmentPropertyStoreRequest;
use App\Http\Responses\AssortmentPropertyCollectionResponse;
use App\Models\AssortmentProperty;
use App\Models\Catalog;
use Illuminate\Http\Response;

class CatalogAssortmentPropertyController extends Controller
{
    /**
     * @param Catalog $catalog
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Catalog $catalog)
    {
        $this->authorize('view', $catalog);

        return AssortmentPropertyCollectionResponse::create(
            $catalog->assortmentProperties()
        );
    }

    /**
     * @param CatalogAssortmentPropertyStoreRequest $request
     * @param Catalog $catalog
     * @param AttachAssortmentPropertyToCatalogContract $service
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(
        CatalogAssortmentPropertyStoreRequest $request,
        Catalog $catalog,
        AttachAssortmentPropertyToCatalogContract $service
    ) {
        $this->authorize('update', $catalog);

        $assortmentProperty = AssortmentProperty::findOrFail($request->assortment_property_uuid);

        $service->attach($catalog, $assortmentProperty);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Catalog $catalog
     * @param AssortmentProperty $assortmentProperty
     * @param DetachAssortmentPropertyFromCatalogContract $service
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(
        Catalog $catalog,
        AssortmentProperty $assortmentProperty,
        DetachAssortmentPropertyFromCatalogContract $service
    ) {
        $this->authorize('update', $catalog);

        $service->detach($catalog, $assortmentProperty);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
