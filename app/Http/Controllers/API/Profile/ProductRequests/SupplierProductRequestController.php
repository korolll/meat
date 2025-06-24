<?php

namespace App\Http\Controllers\API\Profile\ProductRequests;

use App\Events\ProductRequestStatusChanged;
use App\Exceptions\TealsyException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierProductRequestSetConfirmedDateRequest;
use App\Http\Requests\SupplierProductRequestSetStatusRequest;
use App\Http\Resources\SupplierProductRequestResource;
use App\Http\Responses\ProductRequestProductCollectionResponse;
use App\Http\Responses\SupplierProductRequestCollectionResponse;
use App\Models\ProductPreRequest;
use App\Models\ProductRequestDeliveryMethod;
use App\Models\ProductRequestDeliveryStatus;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\ProductRequestSupplierStatus;
use App\Services\Management\ProductRequest\StatusTransitionManagerContract;
use App\Services\Management\Rating\RatingScoreFactoryContract;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class SupplierProductRequestController extends Controller
{
    /**
     * @return mixed
     * @throws TealsyException
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', SupplierProductRequest::class);

        return SupplierProductRequestCollectionResponse::create(
            $this->user->supplierProductRequests()->with(['transportation.car', 'transportation.driver'])
        );
    }

    /**
     * @param SupplierProductRequest $productRequest
     * @return mixed
     * @throws AuthorizationException
     */
    public function show(SupplierProductRequest $productRequest)
    {
        $this->authorize('view', $productRequest);

        return SupplierProductRequestResource::make($productRequest);
    }

    /**
     * @param SupplierProductRequest $productRequest
     * @return mixed
     * @throws TealsyException
     * @throws AuthorizationException
     */
    public function products(SupplierProductRequest $productRequest)
    {
        $this->authorize('view', $productRequest);

        $subQuery = DB::table('product_pre_requests')
            ->select(DB::raw(implode(',', [
                'products.assortment_uuid',
                'product_pre_requests.id',
                'product_pre_requests.product_request_uuid',
                'product_pre_requests.product_uuid',
                'product_pre_requests.quantity',
                'product_pre_requests.status',
                'product_pre_requests.error'
            ])))
            ->join('products', 'products.uuid', '=', 'product_pre_requests.product_uuid');

        $query = $productRequest->products()
            ->leftJoinSub($subQuery, 'pprer', function ($join) {
                /**
                 * @var $join JoinClause
                 */
                $join->on('pprer.product_request_uuid', '=', 'product_product_request.product_request_uuid')
                    ->whereRaw('pprer.assortment_uuid = products.assortment_uuid');
            })->addSelect([
                'pprer.id as product_pre_requests_id',
                'pprer.product_uuid as product_pre_requests_product_uuid',
                'pprer.quantity as product_pre_requests_quantity',
                'pprer.status as product_pre_requests_status',
                'pprer.error as product_pre_requests_error',
            ]);

        return ProductRequestProductCollectionResponse::create($query);
    }

    /**
     * @param SupplierProductRequestSetStatusRequest $request
     * @param SupplierProductRequest $productRequest
     * @return mixed
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function setStatus(SupplierProductRequestSetStatusRequest $request, SupplierProductRequest $productRequest)
    {
        $this->authorize('set-status', $productRequest);

        $originalStatusProductRequest = $productRequest->getOriginal('product_request_supplier_status_id');

        DB::transaction(function () use ($request, $productRequest, $originalStatusProductRequest) {
            app(StatusTransitionManagerContract::class, compact('productRequest'))
                ->transition('product_request_supplier_status_id', $request->product_request_supplier_status_id, $request->getStatusTransitionAttributes())
                ->setConfirmedDate(Carbon::parse($request->confirmed_date))
                ->saveOrFail();

            if ($request->customer_rating) {
                app(RatingScoreFactoryContract::class)->create(
                    $productRequest->customerUser,
                    $this->user,
                    $productRequest,
                    $request->customer_rating
                );
            }

            // Создание презаявки
            $allowedStatusesProductRequestForPreRequest = [
                ProductRequestSupplierStatus::ID_IN_WORK,
                ProductRequestSupplierStatus::ID_SUPPLIER_REFUSED,
                ProductRequestSupplierStatus::ID_USER_CANCELED,
                ProductRequestSupplierStatus::ID_ON_THE_WAY
            ];
            if (is_array($request->pre_request_products)
                && !empty($request->pre_request_products)
                && in_array($productRequest->product_request_supplier_status_id, $allowedStatusesProductRequestForPreRequest, true)
            ) {
                foreach ($request->pre_request_products as $product) {
                    if ((int) $product['quantity'] !== 0) {
                        if (in_array($productRequest->product_request_supplier_status_id, [
                                ProductRequestSupplierStatus::ID_IN_WORK,
                                ProductRequestSupplierStatus::ID_ON_THE_WAY
                            ], true)
                            && $originalStatusProductRequest === ProductRequestSupplierStatus::ID_NEW
                        ) {
                            // Создание презаявки
                            $productPreRequest = ProductPreRequest::make([
                                'user_uuid' => $this->user->uuid,
                                'product_request_uuid' => $productRequest->uuid,
                                'product_uuid' => $product['uuid'],
                                'quantity' => $product['quantity'],
                                'status' => ProductPreRequest::STATUS_NEW,
                                'delivery_date' => Carbon::parse($product['delivery_date']),
                                'confirmed_delivery_date' => Carbon::parse($product['confirmed_delivery_date']),
                            ]);
                            $productPreRequest->save();
                        } else {
                            // Изменение статуса
                            if ($productRequest->product_request_supplier_status_id === ProductRequestSupplierStatus::ID_SUPPLIER_REFUSED) {
                                $productPreRequestStatus = ProductPreRequest::STATUS_SUPPLIER_REFUSED;
                            } elseif ($productRequest->product_request_supplier_status_id === ProductRequestSupplierStatus::ID_USER_CANCELED) {
                                $productPreRequestStatus = ProductPreRequest::STATUS_USER_CANCELED;
                            } else {
                                throw new Exception('Неверный статус');
                            }

                            ProductPreRequest::where([
                                'user_uuid' => $this->user->uuid,
                                'product_request_uuid' => $productRequest->uuid,
                                'product_uuid' => $product['uuid'],
                            ])->update(['status' => $productPreRequestStatus]);
                        }
                    }
                }
            }

            // Экспорт и оповещение пользователей из списка ONE_C_USERS_ALLOWED_TO_EXPORT_ONLY_AFTER_CONFIRMED_DATE в конфиге
            if (
                $productRequest->confirmed_date
                && ($productRequest->product_request_supplier_status_id === ProductRequestSupplierStatus::ID_IN_WORK
                    || ($productRequest->product_request_delivery_method_id === ProductRequestDeliveryMethod::ID_SELF_DELIVERY
                        && $productRequest->product_request_delivery_status_id === ProductRequestDeliveryStatus::ID_IN_WORK
                        && $productRequest->product_request_supplier_status_id === ProductRequestSupplierStatus::ID_ON_THE_WAY
                    )
                )
            ) {
                $userExistsInBlacklist = in_array($this->user->uuid, config('services.1c.users_allowed_to_export_only_after_confirmed_date', []), true);
                if ($userExistsInBlacklist) {
                    ProductRequestStatusChanged::dispatch($productRequest, true, false);
                }
            }
            if ($productRequest->product_request_supplier_status_id === ProductRequestSupplierStatus::ID_SUPPLIER_REFUSED) {
                ProductRequestStatusChanged::dispatch($productRequest, false, true);
            }
        });

        return SupplierProductRequestResource::make($productRequest);
    }

    /**
     * @param SupplierProductRequestSetConfirmedDateRequest $request
     * @param SupplierProductRequest $supplierProductRequest
     * @return SupplierProductRequestResource
     * @throws Throwable
     */
    public function setConfirmedDate(
        SupplierProductRequestSetConfirmedDateRequest $request,
        SupplierProductRequest $supplierProductRequest
    ) {
        $this->authorize('set-confirmed_date', $supplierProductRequest);

        if (!$supplierProductRequest->isStatusSuitableForConfirmedDate()) {
            throw new UnprocessableEntityHttpException('Product request bad status for change field "confirmed_date"');
        }

        DB::transaction(function () use ($request, $supplierProductRequest) {
            if ($request->pre_request_products) {
                // Если все презаявки со статусом NEW
                $statusNew = ProductPreRequest::STATUS_NEW;

                $whereCondition = [
                    'user_uuid' => $this->user->uuid,
                    'product_request_uuid' => $supplierProductRequest->uuid,
                ];

                $countNew = DB::table('product_pre_requests')->selectRaw('count(*) count_new')
                    ->where($whereCondition + ['status' => $statusNew])
                    ->get()
                    ->first()
                    ->count_new;

                $isPreRequestsStatusNew = DB::table('product_pre_requests')
                    ->selectRaw("count(status) = $countNew is_all_new")
                    ->where($whereCondition)
                    ->first('is_all_new')
                    ->is_all_new;

                if ($isPreRequestsStatusNew) {
                    // Удаляем старые
                    ProductPreRequest::where([
                        'user_uuid' => $this->user->uuid,
                        'product_request_uuid' => $supplierProductRequest->uuid,
                    ])->delete();

                    foreach ($request->pre_request_products as $product) {
                        if ((int) $product['quantity'] !== 0) {
                            // Создаём новые
                            $productPreRequest = ProductPreRequest::make($whereCondition + [
                                    'product_uuid' => $product['uuid'],
                                    'quantity' => $product['quantity'],
                                    'status' => ProductPreRequest::STATUS_NEW,
                                    'delivery_date' => Carbon::parse($product['delivery_date']),
                                    'confirmed_delivery_date' => Carbon::parse($product['confirmed_delivery_date']),
                                ]);
                            $productPreRequest->save();
                        }
                    }
                } else {
                    throw new UnprocessableEntityHttpException('Одна или несколько презаявок уже обработы. Обновление невозможно.');
                }
            }

            $supplierProductRequest->setConfirmedDate(Carbon::parse($request->confirmed_date));
            $supplierProductRequest->save();

            // Экспорт и оповещение пользователей из списка в конфиге
            if ($request->confirmed_date) {
                ProductRequestStatusChanged::dispatch($supplierProductRequest, true, false);
            }
        });

        return SupplierProductRequestResource::make($supplierProductRequest);
    }
}
