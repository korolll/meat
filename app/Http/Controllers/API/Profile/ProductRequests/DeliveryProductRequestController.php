<?php

namespace App\Http\Controllers\API\Profile\ProductRequests;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryProductRequestStoreRequest;
use App\Http\Resources\DeliveryProductRequestResource;
use App\Http\Responses\DeliveryProductRequestCollectionResponse;
use App\Http\Responses\ProductRequestProductCollectionResponse;
use App\Models\ProductRequests\DeliveryProductRequest;
use App\Services\Management\ProductRequest\DeliveryUserApplierContract;
use Illuminate\Support\Facades\DB;

class DeliveryProductRequestController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', DeliveryProductRequest::class);

        return DeliveryProductRequestCollectionResponse::create(
            $this->user->deliveryProductRequests()
                ->with([
                    'transportation.car',
                    'transportation.driver'
                ])
        );
    }

    /**
     * @param DeliveryProductRequestStoreRequest $request
     * @return DeliveryProductRequestResource
     * @throws \Throwable
     */
    public function store(DeliveryProductRequestStoreRequest $request)
    {
        $this->authorize('create', DeliveryProductRequest::class);

        // @todo В дальнейшем нужно зарефакторить, это должно переехать в отдельный сервис
        return DB::transaction(function () use ($request) {
            $productRequest = DeliveryProductRequest::lock()->findOrFail($request->product_request_uuid);

            app(DeliveryUserApplierContract::class, compact('productRequest'))
                ->apply($this->user, $request->delivery_comment)
                ->save();

            return DeliveryProductRequestResource::make($productRequest);
        });
    }

    /**
     * @param DeliveryProductRequest $productRequest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(DeliveryProductRequest $productRequest)
    {
        $this->authorize('view', $productRequest);

        return DeliveryProductRequestResource::make($productRequest);
    }

    /**
     * @param DeliveryProductRequest $productRequest
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function products(DeliveryProductRequest $productRequest)
    {
        $this->authorize('view', $productRequest);

        return ProductRequestProductCollectionResponse::create($productRequest->products());
    }
}
