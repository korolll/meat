<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountForbiddenCatalogBulkStoreRequest;
use App\Http\Requests\DiscountForbiddenCatalogStoreRequest;
use App\Http\Resources\DiscountForbiddenCatalogResource;
use App\Http\Responses\DiscountForbiddenCatalogCollectionResponse;
use App\Models\DiscountForbiddenCatalog;
use Illuminate\Support\Facades\DB;

class DiscountForbiddenCatalogController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', DiscountForbiddenCatalog::class);

        return DiscountForbiddenCatalogCollectionResponse::create(
            DiscountForbiddenCatalog::query()
        );
    }

    /**
     * @param \App\Http\Requests\DiscountForbiddenCatalogStoreRequest $request
     *
     * @return \App\Http\Resources\DiscountForbiddenCatalogResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(DiscountForbiddenCatalogStoreRequest $request)
    {
        $this->authorize('create', DiscountForbiddenCatalog::class);

        $discountForbiddenCatalog = new DiscountForbiddenCatalog($request->validated());
        $discountForbiddenCatalog->saveOrFail();

        return DiscountForbiddenCatalogResource::make($discountForbiddenCatalog);
    }

    /**
     * @param \App\Http\Requests\DiscountForbiddenCatalogBulkStoreRequest $request
     *
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function storeBulk(DiscountForbiddenCatalogBulkStoreRequest $request)
    {
        $this->authorize('create', DiscountForbiddenCatalog::class);

        $catalogUuids = $request->validated()['catalog_uuids'];
        $collection = DB::transaction(function () use ($catalogUuids) {
            $collection = [];
            foreach ($catalogUuids as $catalogUuid) {
                $discountForbiddenCatalog = new DiscountForbiddenCatalog();
                $discountForbiddenCatalog->catalog_uuid = $catalogUuid;
                $discountForbiddenCatalog->saveOrFail();
                $collection[] = $discountForbiddenCatalog;
            }

            return $collection;
        });

        return DiscountForbiddenCatalogResource::collection($collection);
    }

    /**
     * @param \App\Models\DiscountForbiddenCatalog $discountForbiddenCatalog
     *
     * @return \App\Http\Resources\DiscountForbiddenCatalogResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(DiscountForbiddenCatalog $discountForbiddenCatalog)
    {
        $this->authorize('view', $discountForbiddenCatalog);

        return DiscountForbiddenCatalogResource::make($discountForbiddenCatalog);
    }

    /**
     * @param \App\Models\DiscountForbiddenCatalog $discountForbiddenCatalog
     *
     * @return \App\Http\Resources\DiscountForbiddenCatalogResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(DiscountForbiddenCatalog $discountForbiddenCatalog)
    {
        $this->authorize('delete', $discountForbiddenCatalog);
        $discountForbiddenCatalog->delete();

        return DiscountForbiddenCatalogResource::make($discountForbiddenCatalog);
    }
}
