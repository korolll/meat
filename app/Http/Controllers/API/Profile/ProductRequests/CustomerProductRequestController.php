<?php

namespace App\Http\Controllers\API\Profile\ProductRequests;

use App\Exports\CustomerProductRequestExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerProductRequestSetStatusRequest;
use App\Http\Requests\CustomerProductRequestStoreRequest;
use App\Http\Requests\CustomerProductRequestUpdateRequest;
use App\Http\Resources\CustomerProductRequestResource;
use App\Http\Resources\CustomerProductRequestResourceCollection;
use App\Http\Responses\CustomerProductRequestCollectionResponse;
use App\Http\Responses\ProductRequestProductCollectionResponse;
use App\Models\Product;
use App\Models\ProductPreRequest;
use App\Models\ProductRequests\CustomerProductRequest;
use App\Services\Management\ProductRequest\ProductRequestExpectedDeliveryDateValidatorContract;
use App\Services\Management\ProductRequest\ProductRequestWrapper;
use App\Services\Management\ProductRequest\StatusTransitionManagerContract;
use App\Services\Management\Rating\RatingScoreFactoryContract;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CustomerProductRequestController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', CustomerProductRequest::class);

        return CustomerProductRequestCollectionResponse::create(
            $this->user->customerProductRequests()
                ->with([
                    'transportation.car',
                    'transportation.driver'
                ])
        );
    }

    /**
     * @param CustomerProductRequestStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CustomerProductRequestStoreRequest $request)
    {
        $this->authorize('create', CustomerProductRequest::class);

        $productRequestUuids = \Illuminate\Database\Eloquent\Collection::make();
        // @todo В дальнейшем нужно зарефакторить, это должно переехать в отдельный сервис
        DB::transaction(function () use ($request, $productRequestUuids) {
            /**
             * @var $productRequestWrappers ProductRequestWrapper[]|Collection
             */
            $productRequestWrappers = $request->asProductRequestFactory()->make();

            if ($productRequestWrappers->count() === 0) {
                throw new UnprocessableEntityHttpException('Ошибка при создании');
            }

            foreach ($productRequestWrappers as $productRequestWrapper) {
                $productRequestWrapper->setCustomerUser($this->user);
                $productRequest = $productRequestWrapper->saveOrFail();
                if ($productRequest) {
                    $productRequestUuids->push(CustomerProductRequestResource::make($productRequest));
                }
            }

            if ($request->supplier_product_requests) {
                $supplierProductRequestsUuids = Arr::pluck($request->supplier_product_requests ?? [], 'uuid');
                // Меняем статус связанных презаявок
                ProductPreRequest::whereIn('product_request_uuid', $supplierProductRequestsUuids)
                    ->update(['status' => ProductPreRequest::STATUS_HAND_PRODUCT_REQUEST]);
            }
        });

        return response(CustomerProductRequestResourceCollection::make($productRequestUuids), Response::HTTP_CREATED);
    }

    /**
     * @param CustomerProductRequest $productRequest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(CustomerProductRequest $productRequest)
    {
        $this->authorize('view', $productRequest);

        return CustomerProductRequestResource::make($productRequest);
    }

    /**
     * @param CustomerProductRequest $productRequest
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function products(CustomerProductRequest $productRequest)
    {
        $this->authorize('view', $productRequest);

        return ProductRequestProductCollectionResponse::create($productRequest->products());
    }

    /**
     * @param CustomerProductRequestUpdateRequest $request
     * @param CustomerProductRequest $productRequest
     * @param string $productUuid
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateProduct(CustomerProductRequestUpdateRequest $request, CustomerProductRequest $productRequest, $productUuid)
    {
        $this->authorize('update', $productRequest);

        $count = $productRequest->products()->updateExistingPivot($productUuid, [
            'quantity_actual' => $request->quantity_actual,
        ]);
        if (!$count) {
            $product = Product::find($productUuid);
            // @todo доделать проверку по активным прайсам поставщика
            $isProductBelongsToSupplier = $product->user_uuid === $productRequest->supplier_user_uuid;
            if ($isProductBelongsToSupplier) {
                $productRequest->products()->attach($productUuid, [
                    'quantity' => 0,
                    'quantity_actual' => $request->quantity_actual,
                    'price' => $product->price,
                    'weight' => $product->assortment->weight,
                    'volume' => $product->volume,
                    'is_added_product' => true
                ]);
                $count = 1;
            }
        }

        return response('', $count ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }

    /**
     * @param CustomerProductRequestSetStatusRequest $request
     * @param CustomerProductRequest $productRequest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setStatus(CustomerProductRequestSetStatusRequest $request, CustomerProductRequest $productRequest)
    {
        $this->authorize('set-status', $productRequest);

        if ($request->expected_delivery_date) {
            $dateValidator = app(ProductRequestExpectedDeliveryDateValidatorContract::class);
            $expectedDeliveryDate = Date::parse($request->expected_delivery_date);
            $dateValidator->validate($expectedDeliveryDate, $productRequest->products->all());
        }

        DB::transaction(function () use ($request, $productRequest) {
            app(StatusTransitionManagerContract::class, compact('productRequest'))
                ->transition('product_request_customer_status_id', $request->product_request_customer_status_id, $request->getStatusTransitionAttributes())
                ->saveOrFail();

            if ($request->supplier_rating) {
                app(RatingScoreFactoryContract::class)->create(
                    $productRequest->supplierUser,
                    $this->user,
                    $productRequest,
                    $request->supplier_rating
                );
            }
        });

        return CustomerProductRequestResource::make($productRequest);
    }

    /**
     * @param CustomerProductRequest $productRequest
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function export(CustomerProductRequest $productRequest)
    {
        $this->authorize('view', $productRequest);

        return (new CustomerProductRequestExport($productRequest))->download('customer_request.xlsx');
    }
}
