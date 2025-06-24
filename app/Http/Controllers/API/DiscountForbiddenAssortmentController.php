<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountForbiddenAssortmentBulkStoreRequest;
use App\Http\Requests\DiscountForbiddenAssortmentStoreRequest;
use App\Http\Resources\DiscountForbiddenAssortmentResource;
use App\Http\Responses\DiscountForbiddenAssortmentCollectionResponse;
use App\Models\DiscountForbiddenAssortment;
use Illuminate\Support\Facades\DB;

class DiscountForbiddenAssortmentController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', DiscountForbiddenAssortment::class);

        return DiscountForbiddenAssortmentCollectionResponse::create(
            DiscountForbiddenAssortment::query()
        );
    }

    /**
     * @param \App\Http\Requests\DiscountForbiddenAssortmentStoreRequest $request
     *
     * @return \App\Http\Resources\DiscountForbiddenAssortmentResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(DiscountForbiddenAssortmentStoreRequest $request)
    {
        $this->authorize('create', DiscountForbiddenAssortment::class);

        $discountForbiddenAssortment = new DiscountForbiddenAssortment($request->validated());
        $discountForbiddenAssortment->saveOrFail();

        return DiscountForbiddenAssortmentResource::make($discountForbiddenAssortment);
    }

    /**
     * @param DiscountForbiddenAssortmentBulkStoreRequest $request
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function storeBulk(DiscountForbiddenAssortmentBulkStoreRequest $request)
    {
        $this->authorize('create', DiscountForbiddenAssortment::class);

        $assortmentUuids = $request->validated()['assortment_uuids'];
        $collection = DB::transaction(function () use ($assortmentUuids) {
            $collection = [];
            foreach ($assortmentUuids as $assortmentUuid) {
                $discountForbiddenAssortment = new DiscountForbiddenAssortment();
                $discountForbiddenAssortment->assortment_uuid = $assortmentUuid;
                $discountForbiddenAssortment->saveOrFail();
                $collection[] = $discountForbiddenAssortment;
            }

            return $collection;
        });

        return DiscountForbiddenAssortmentResource::collection($collection);
    }

    /**
     * @param \App\Models\DiscountForbiddenAssortment $discountForbiddenAssortment
     *
     * @return \App\Http\Resources\DiscountForbiddenAssortmentResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(DiscountForbiddenAssortment $discountForbiddenAssortment)
    {
        $this->authorize('view', $discountForbiddenAssortment);

        return DiscountForbiddenAssortmentResource::make($discountForbiddenAssortment);
    }

    /**
     * @param \App\Models\DiscountForbiddenAssortment $discountForbiddenAssortment
     *
     * @return \App\Http\Resources\DiscountForbiddenAssortmentResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(DiscountForbiddenAssortment $discountForbiddenAssortment)
    {
        $this->authorize('delete', $discountForbiddenAssortment);
        $discountForbiddenAssortment->delete();

        return DiscountForbiddenAssortmentResource::make($discountForbiddenAssortment);
    }
}
