<?php

namespace App\Console\Commands;

use App\Mail\ProductPreRequestErrorMail;
use App\Models\ProductPreRequest;
use App\Models\ProductPreRequestCustomerSupplier;
use App\Models\ProductRequestDeliveryMethod;
use App\Models\User;
use App\Services\Management\ProductRequest\ProductRequestWrapper;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Mail;
use Throwable;

class ProductPreRequestsCreateSupplierRequestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product-pre-request:create-supplier-request';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание заявки на поставку из предзаявок';
    /**
     * @var ProductPreRequest[]|Collection
     */
    protected $badProductPreRequests;

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     * @throws Throwable
     */
    public function handle()
    {
        $this->badProductPreRequests = collect();
        /*
        =================================================================

            1. выбрать все по числу на сегодня
            2. группируем по product_pre_requests.user_uuid
            3. группируем по product.user_uuid
            4. выбираем минимальную дату в группах по product.user_uuid
            5. Если дата и время <= текущее, то создаём заявку, включая туда всю группу, иначе ничего не делаем
            6. сколько уникальных confirmed_del_date, столько и делаем заявок

        ===================================================================

            product_pre_requests.user_uuid - от какого пользователя сделать заявку.

            сделать выборку и сгруппировать по product_pre_requests.user_uuid | products.user_uuid. Дальше вытаскивать, где product_pre_requests.user_uuid | products.user_uuid
                = данные из прошлой выборки

            если при группировке по "product_pre_requests.user_uuid  &&  products.user_uuid  &&  product_pre_requests.product_uuid  &&  product_pre_requests.confirmed_delivery_date"
                находятся одинаковые, то объединяем презаявки в одну, b суммируем quantity
         */

        $nowDateTime = Carbon::now()->addHours(1)->toDateTimeString();
        $thisDay = Carbon::today()->toDateTimeString();
        $nextDay = Carbon::tomorrow()->toDateTimeString();

        $prepareQueryData = DB::table(new Expression('product_pre_requests ppr'))
            ->select(
                new Expression('p.user_uuid product_user_uuid'),
                new Expression('ppr.user_uuid ppr_user_uuid')
            )
            ->leftJoin(new Expression('products p'), 'p.uuid', '=', 'ppr.product_uuid')
            ->whereBetween('ppr.delivery_date', [$thisDay, $nextDay])
            ->where('ppr.status', '=', ProductPreRequest::STATUS_NEW)
            ->groupBy('p.user_uuid', 'ppr.user_uuid', 'ppr.product_uuid')
            ->having(new Expression('MIN(ppr.delivery_date)'), '<=', $nowDateTime)
            ->get();

        foreach ($prepareQueryData as $prepareQueryDatum) {
            /**
             * @var $productPreRequestsCollection ProductPreRequest[]|Collection
             */
            $productPreRequestsCollection = ProductPreRequest::query()
                ->select(new Expression('product_pre_requests.*'))
                ->with(['product', 'user'])
                ->leftJoin(new Expression('products'), 'products.uuid', '=', 'product_pre_requests.product_uuid')
                ->whereBetween('product_pre_requests.delivery_date', [$thisDay, $nextDay])
                ->where('product_pre_requests.status', '=', ProductPreRequest::STATUS_NEW)
                ->where('product_pre_requests.user_uuid', '=', $prepareQueryDatum->ppr_user_uuid)
                ->where('products.user_uuid', '=', $prepareQueryDatum->product_user_uuid)
                ->get();

            if ($productPreRequestsCollection->count() > 0) {
                /**
                 * если при группировке по "product_pre_requests.user_uuid  &&  product_pre_requests.product_uuid  &&  product_pre_requests.confirmed_delivery_date"
                 * находятся одинаковые, то объединяем презаявки в одну, и суммируем quantity
                 *
                 * @var $unique Collection|ProductPreRequest[]
                 */
                $unique = [];
                foreach ($productPreRequestsCollection as $productPreRequest) {
                    $key = $productPreRequest->user_uuid
                        . '|||' . $productPreRequest->product_uuid
                        . '|||' . $productPreRequest->product->user_uuid
                        . '|||' . $productPreRequest->confirmed_delivery_date->toDateTimeString();

                    if (array_key_exists($key, $unique)) {
                        // Если есть дубликат
                        $unique[$key]->quantity += $productPreRequest->quantity;
                        $productPreRequest->update(['status' => ProductPreRequest::STATUS_DONE]);
                    } else {
                        $unique[$key] = $productPreRequest;
                    }
                }

                $productPreRequestsCollection = collect($unique)->groupBy([
                    static function ($item) {
                        /**
                         * @var $item ProductPreRequest
                         */
                        return $item->confirmed_delivery_date->toDateTimeString();
                    },
                    'product.user_uuid'
                ], true);

                unset($unique);

                foreach ($productPreRequestsCollection as $confirmed_delivery_date => $preRequestsByProductUserUuid) {
                    foreach ($preRequestsByProductUserUuid as $uniqueKey => $preRequests) {
                        try {
                            DB::transaction(function () use ($preRequests, $confirmed_delivery_date) {
                                $products = [];
                                $supplierProductRequestUuids = [];
                                $customerUser = null;
                                foreach ($preRequests as $productPreRequest) {
                                    /**
                                     * @var $productPreRequest ProductPreRequest
                                     */
                                    if ($productPreRequest->product->price === null) {
                                        $productPreRequest->product->price = $productPreRequest->product->priceLists()->pluck('price_new')->max();
                                    }

                                    $products[] = [
                                        'product' => $productPreRequest->product,
                                        'quantity' => $productPreRequest->quantity
                                    ];
                                    $customerUser = $productPreRequest->user;
                                    $productPreRequest->quantity = $productPreRequest->getOriginal('quantity');
                                    $productPreRequest->update(['status' => ProductPreRequest::STATUS_DONE]);
                                    // Связываем заявку на поставку с заявкой на отгрузку через презаявки
                                    $supplierProductRequestUuids[] = $productPreRequest->product_request_uuid;
                                }

                                $productRequest = $this->makeProductRequest(
                                    Date::parse($confirmed_delivery_date),
                                    collect($products),
                                    collect($supplierProductRequestUuids)->unique()
                                )
                                    ->setCustomerUser($customerUser)
                                    ->saveOrFail();

                                // Запись связки пользователей для отображения в продуктах флага, заказан продукт или нет
                                $newProductPreRequestCustomerSupplier = [
                                    'customer_user_uuid' => $customerUser->uuid,
                                    'supplier_user_uuid' => $productRequest->supplier_user_uuid,
                                ];
                                $isExists = ProductPreRequestCustomerSupplier::where($newProductPreRequestCustomerSupplier)->exists();
                                if (!$isExists) {
                                    ProductPreRequestCustomerSupplier::make($newProductPreRequestCustomerSupplier)->save();
                                }
                            });
                        } catch (\Exception $tryError) {
                            foreach ($preRequests as $productPreRequest) {
                                $productPreRequest->update([
                                    'status' => ProductPreRequest::STATUS_ERROR,
                                    'error' => $tryError->getMessage()
                                ]);
                                $productPreRequest->load([
                                    'product.assortment',
                                    'product.user',
                                    'user',
                                    'productRequest',
                                ]);
                                $this->badProductPreRequests->add($productPreRequest);
                            }
                        }
                    }
                }
            }
        }

        if ($this->badProductPreRequests->count() > 0) {
            $this->notifyByErrors();
        }
    }

    /**
     * @throws \Exception
     */
    protected function notifyByErrors()
    {
        $usersToNotifyUuids = config('services.notifications.product_pre_request_error_users_uuids', []);
        $usersToNotify = User::whereIn('uuid', $usersToNotifyUuids)->get();
        if (!$usersToNotify) {
            throw new \Exception('Не найден параметр PRODUCT_PRE_REQUEST_ERROR_USERS_UUIDS в конфиге');
        }
        Mail::send(new ProductPreRequestErrorMail($this->badProductPreRequests));
    }

    /**
     * @param CarbonInterface $expectedDeliveryDate
     * @param Collection $products
     * @param $supplierProductRequestUuids
     * @return ProductRequestWrapper|\Illuminate\Contracts\Foundation\Application|mixed
     * @throws \App\Exceptions\TealsyException
     */
    protected function makeProductRequest(CarbonInterface $expectedDeliveryDate, Collection $products, $supplierProductRequestUuids)
    {
        $request = app(ProductRequestWrapper::class);

        foreach ($products as $product) {
            $request->attachProduct($product['product'], $product['quantity']);
        }

        $request->setExpectedDeliveryDate($expectedDeliveryDate);
        $request->setDeliveryMethodId(ProductRequestDeliveryMethod::ID_DELIVERY);
        $request->setSupplierProductsRequestUuids($supplierProductRequestUuids);

        return $request;
    }
}
