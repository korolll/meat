<?php

namespace Tests\Feature\API\Profile\ProductRequests;

use App\Events\ProductRequestCreated;
use App\Events\ProductRequestStatusChanged;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductPreRequest;
use App\Models\ProductRequest;
use App\Models\ProductRequestDeliveryMethod;
use App\Models\ProductRequestDeliveryStatus;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\ProductRequestSupplierStatus;
use App\Models\RatingType;
use App\Models\User;
use App\Notifications\API\SupplierProductRequestStatusDoneOrRefused;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCaseNotificationsFake;

class SupplierProductRequestTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $request = factory(ProductRequest::class)->create();

        $self = $request->supplierUser;
        $json = $this->be($self)->getJson('/api/profile/product-requests/supplier');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $request->uuid,
                    'product_request_delivery_method_id' => $request->product_request_delivery_method_id,
                ],
            ],
        ]);
        $data = json_decode($json->getContent(), true);
        $this->assertArrayHasKey('expected_delivery_date', $data['data'][0]);
    }

    /**
     * @test
     */
    public function show()
    {
        $request = factory(ProductRequest::class)->create();

        $self = $request->supplierUser;
        $json = $this->be($self)->getJson("/api/profile/product-requests/supplier/{$request->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $request->uuid,
                'product_request_delivery_method_id' => $request->product_request_delivery_method_id,
            ],
        ]);
    }

    /**
     * @test
     */
    public function products()
    {
        $request = factory(ProductRequest::class)->create();
        $product = factory(Product::class)->create(['user_uuid' => $request->supplier_user_uuid]);
        $quantity = 1;

        $request->products()->attach($product, [
            'quantity' => $quantity,
            'quantity_actual' => $quantity,
            'price' => $product->price,
        ]);

        $self = $request->supplierUser;
        $json = $this->be($self)->getJson("/api/profile/product-requests/supplier/{$request->uuid}/products");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'product_uuid' => $product->uuid,
                    'quantity' => $quantity,
                    'quantity_actual' => $quantity,
                ],
            ],
        ]);
    }

    /**
     * @param int $quantityOld
     * @param int $quantityInRequest
     * @param int $quantityNew
     *
     * @test
     * @dataProvider setStatusProvider
     */
    public function setStatus($quantityOld, $quantityInRequest, $quantityNew)
    {
        $request = factory(ProductRequest::class)->create([
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_ON_THE_WAY,
        ]);

        $product = factory(Product::class)->create([
            'user_uuid' => $request->supplier_user_uuid,
            'quantity' => $quantityOld,
        ]);

        $request->products()->attach($product, [
            'quantity' => $quantityInRequest,
            'quantity_actual' => $quantityInRequest,
            'price' => $product->price
        ]);

        $self = $request->supplierUser;
        $json = $this->be($self)->putJson("/api/profile/product-requests/supplier/{$request->uuid}/set-status", [
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_DONE,
            'customer_rating' => 5,
            'supplier_comment' => 'test_comment'
        ]);

        $data = [
            'uuid' => $request->uuid,
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_DONE,
            'supplier_comment' => 'test_comment'
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('product_requests', $data);

        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $quantityOld,
            'quantity_delta' => -1 * $quantityInRequest,
            'quantity_new' => $quantityNew,
        ]);

        $this->assertDatabaseHas('products', [
            'uuid' => $product->uuid,
            'quantity' => $quantityNew,
        ]);

        $this->assertDatabaseHas('rating_scores', [
            'rated_reference_type' => User::MORPH_TYPE_ALIAS,
            'rated_reference_id' => $request->customer_user_uuid,
            'rated_by_reference_type' => User::MORPH_TYPE_ALIAS,
            'rated_by_reference_id' => $request->supplier_user_uuid,
            'rated_through_reference_type' => SupplierProductRequest::MORPH_TYPE_ALIAS,
            'rated_through_reference_id' => $request->uuid,
            'value' => 5,
        ]);

        $this->assertDatabaseHas('ratings', [
            'reference_type' => User::MORPH_TYPE_ALIAS,
            'reference_id' => $request->customer_user_uuid,
            'rating_type_id' => RatingType::ID_CUSTOMER,
            'value' => 5,
        ]);
    }

    /**
     * @test
     */
    public function setStatusWithDate()
    {
        $request = factory(ProductRequest::class)->create([
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_NEW,
        ]);
        $expected_date = now()->addMinute();

        $self = $request->supplierUser;
        $json = $this->be($self)->putJson("/api/profile/product-requests/supplier/{$request->uuid}/set-status", [
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_MATCHING,
            'expected_delivery_date' => $expected_date,
        ]);

        $data = [
            'uuid' => $request->uuid,
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_MATCHING,
            'expected_delivery_date' => $expected_date,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('product_requests', $data);
    }


    /**
     * @test
     */
    public function setStatusForSelfDelivery()
    {
        Event::fake([ProductRequestStatusChanged::class]);

        /**
         * @var $request ProductRequest
         */
        $request = factory(ProductRequest::class)->state('self-delivery')->create([
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_NEW,
        ]);
        $self = $request->supplierUser;
        \Config::set('services.1c.users_allowed_to_export_only_after_confirmed_date', [$request->supplier_user_uuid]);

        $product = factory(Product::class)->create([
            'user_uuid' => $request->supplier_user_uuid
        ]);

        $quantity = 10;
        $request->products()->attach($product, [
            'quantity' => $quantity,
            'quantity_actual' => $quantity,
            'price' => $product->price,
        ]);

        $confirmedDate = (string) now()->addMonth()->subDay();

        $json = $this->be($self)->putJson("/api/profile/product-requests/supplier/{$request->uuid}/set-status", [
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_IN_WORK,
            'confirmed_date' => $confirmedDate
        ]);

        $data = [
            'uuid' => $request->uuid,
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_ON_THE_WAY,
            'product_request_delivery_status_id' => ProductRequestDeliveryStatus::ID_IN_WORK,
            'confirmed_date' => $confirmedDate
        ];

        $json->assertSuccessful()->assertJson(compact('data'));

        Event::assertDispatched(ProductRequestStatusChanged::class, 1);
    }

    /**
     * @param string $status
     * @param null|string $comment
     * @param bool $assertSuccess
     * @test
     * @testWith ["done", null, true]
     *           ["supplier-refused", null, false]
     *           ["supplier-refused", "test", true]
     */
    public function setStatusCanceledOrDoneAndCheckNotification(string $status, ?string $comment, bool $assertSuccess)
    {
        $request = factory(ProductRequest::class)->create([
            'product_request_supplier_status_id' => $status === ProductRequestSupplierStatus::ID_DONE ? ProductRequestSupplierStatus::ID_ON_THE_WAY : ProductRequestSupplierStatus::ID_NEW,
        ]);
        $product = factory(Product::class)->create([
            'user_uuid' => $request->supplier_user_uuid,
        ]);

        $quantity = 10;
        $request->products()->attach($product, [
            'quantity' => $quantity,
            'quantity_actual' => $quantity,
            'price' => $product->price,
        ]);

        $self = $request->supplierUser;
        $json = $this->be($self)->putJson("/api/profile/product-requests/supplier/{$request->uuid}/set-status", [
            'product_request_supplier_status_id' => $status,
            'supplier_comment' => $comment,
            'customer_rating' => 5,
        ]);

        if (!$assertSuccess) {
            $json->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

            return;
        }

        \Notification::assertSentTo($request->customerUser, SupplierProductRequestStatusDoneOrRefused::class);
        $data = [
            'uuid' => $request->uuid,
            'product_request_supplier_status_id' => $status,
            'supplier_comment' => $comment
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('product_requests', $data);
    }

    /**
     * Данные: [quantityOld, quantityInRequest, quantityNew]
     * Расчет: quantityNew = quantityOld - quantityInRequest
     *
     * @return array
     */
    public function setStatusProvider()
    {
        return [
            [10, 5, 5],
        ];
    }

    /**
     * @test
     */
    public function setConfirmedDate()
    {
        $deliveryDate = now()->addDays(10);
        /**
         * @var $supplierProductRequest SupplierProductRequest
         */
        $supplierProductRequest = factory(SupplierProductRequest::class)->state('confirmed_date')->create();
//        $productPreRequests = $supplierProductRequest->productPreRequests;
        $self = $supplierProductRequest->supplierUser;
        /**
         * @var $product3 Product
         */
        $product3 = factory(Product::class)->create([
            'user_uuid' => $supplierProductRequest->supplierUser->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$deliveryDate->dayOfWeek],
            'price' => 20
        ]);

        /**
         * @var $priceList1 PriceList
         */
        $priceList1 = factory(PriceList::class)->create([
            'user_uuid' => $supplierProductRequest->supplierUser->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);

        $priceList1->products()->attach([
            $product3->uuid => ['price_new' => 300],
        ]);

        $confirmedDate = (string) now()->addMonth()->subDay();

        $preRequestProducts = [
            'quantity' => $product3->quantum * 10,
            'delivery_date' => $deliveryDate->subMinute()->toDateTimeString(),
            'confirmed_delivery_date' => $deliveryDate->addMinute()->toDateTimeString(),
        ];

        $json = $this->be($self)
            ->putJson("/api/profile/product-requests/supplier/{$supplierProductRequest->uuid}/set-confirmed-date", [
                'confirmed_date' => $confirmedDate,
                'pre_request_products' => [
                    array_merge([
                        'uuid' => $product3->uuid,
                    ], $preRequestProducts)
                ]
            ]);

        $data = [
            'uuid' => $supplierProductRequest->uuid,
            'confirmed_date' => $confirmedDate
        ];

        $json->assertSuccessful()->assertJson(compact('data'));

        $supplierProductRequest->products->map(function (Product $product) use ($self, $supplierProductRequest, $preRequestProducts) {
            $this->assertDatabaseMissing('product_pre_requests', array_merge([
                'user_uuid' => $self->uuid,
                'product_request_uuid' => $supplierProductRequest->uuid,
                'product_uuid' => $product->uuid,
            ], $preRequestProducts));
        });

        $this->assertDatabaseHas('product_pre_requests', array_merge([
            'user_uuid' => $self->uuid,
            'product_request_uuid' => $supplierProductRequest->uuid,
            'product_uuid' => $product3->uuid,
            'status' => ProductPreRequest::STATUS_NEW,
        ], $preRequestProducts));

        //////////////////////////////////////////

        $json = $this->be($self)
            ->putJson("/api/profile/product-requests/supplier/{$supplierProductRequest->uuid}/set-confirmed-date", [
                'confirmed_date' => $confirmedDate,
            ]);

        $data = [
            'uuid' => $supplierProductRequest->uuid,
            'confirmed_date' => $confirmedDate
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setStatusInWork()
    {
        \Storage::fake('testing');

        $product_price = 300;
        $supplierUser1 = factory(User::class)->state('supplier')->create();
        $supplierUser2 = factory(User::class)->state('supplier')->create();
        $storeUser = factory(User::class)->state('store')->create();

        $expectedDate1 = now()->addDays();

        \Config::set('services.1c.users_allowed_to_export_only_after_confirmed_date', [$supplierUser1->uuid, $supplierUser2->uuid]);

        /////////////////////////// PRODUCT 1 ///////////////////////////////

        $product1 = factory(Product::class)->create([
            'user_uuid' => $supplierUser1->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => null,
            'volume' => 1000,
        ]);

        $priceList1 = factory(PriceList::class)->create([
            'user_uuid' => $supplierUser1->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);
        $priceList1->products()->attach([
            $product1->uuid => ['price_new' => $product_price],
        ]);

        $this->expectsEvents(ProductRequestCreated::class);

        $json = $this->be($storeUser)->postJson('/api/profile/product-requests/customer', [
            'products' => [
                [
                    'product_uuid' => $product1->uuid,
                    'quantity' => $product1->min_quantity_in_order,
                    'expected_delivery_date' => $expectedDate1,
                ],
            ],
            'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_DELIVERY,
        ]);

        $productRequestUuids = json_decode($json->baseResponse->getContent(), true);
        $this->assertNotEmpty($productRequestUuids);
        $json->assertStatus(Response::HTTP_CREATED);
        $json->assertSuccessful();

        $this->assertDatabaseHas('product_requests', [
            'customer_user_uuid' => $storeUser->uuid,
            'expected_delivery_date' => $expectedDate1,
            'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_DELIVERY,
            'price' => $product_price * $product1->min_quantity_in_order,
            'volume' => $product1->volume * $product1->min_quantity_in_order,
        ]);

        $this->assertDatabaseHas('product_product_request', [
            'product_uuid' => $product1->uuid,
            'quantity' => $product1->min_quantity_in_order,
            'quantity_actual' => $product1->min_quantity_in_order,
            'price' => $product_price
        ]);

        /////////////////////////// PRODUCT 2 ///////////////////////////////

        $product2 = factory(Product::class)->create([
            'user_uuid' => $supplierUser2->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => null,
            'volume' => 1000,
        ]);

        $priceList2 = factory(PriceList::class)->create([
            'user_uuid' => $supplierUser2->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);
        $priceList2->products()->attach([
            $product2->uuid => ['price_new' => $product_price],
        ]);

        $supplierProductRequest = factory(ProductRequest::class)->create([
            'supplier_user_uuid' => $storeUser->uuid,
        ]);

        $this->expectsEvents(ProductRequestCreated::class);

        $json = $this->be($storeUser)->postJson('/api/profile/product-requests/customer', [
            'supplier_product_requests' => [
                [
                    'uuid' => $supplierProductRequest->uuid,
                ]
            ],
            'products' => [
                [
                    'product_uuid' => $product2->uuid,
                    'quantity' => $product2->min_quantity_in_order,
                    'expected_delivery_date' => $expectedDate1,
                ],
            ],
            'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_DELIVERY,
        ]);

        $productRequestUuids = json_decode($json->baseResponse->getContent(), true);
        $this->assertNotEmpty($productRequestUuids);
        $productRequestUuid = @current($productRequestUuids)['uuid'];
        $json->assertStatus(Response::HTTP_CREATED);
        $json->assertSuccessful();
        $this->assertDatabaseHas('product_requests', [
            'customer_user_uuid' => $storeUser->uuid,
            'expected_delivery_date' => $expectedDate1,
            'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_DELIVERY,
            'price' => $product_price * $product2->min_quantity_in_order,
            'volume' => $product2->volume * $product2->min_quantity_in_order,
        ]);

        $this->assertDatabaseHas('product_product_request', [
            'product_uuid' => $product2->uuid,
            'quantity' => $product2->min_quantity_in_order,
            'quantity_actual' => $product2->min_quantity_in_order,
            'price' => $product_price
        ]);

        $this->assertDatabaseHas('customer_product_request_supplier_product_request', [
            'supplier_product_request_uuid' => $supplierProductRequest->uuid,
        ]);

        $this->expectsEvents(ProductRequestStatusChanged::class);

        $confirmedDate = (string) now()->addMonth()->subDay();
        $status = ProductRequestSupplierStatus::ID_IN_WORK;
        $json = $this->be($supplierUser2)->putJson("/api/profile/product-requests/supplier/{$productRequestUuid}/set-status", [
            'product_request_supplier_status_id' => $status,
            'confirmed_date' => $confirmedDate,
            'pre_request_products' => [
                [
                    'uuid' => $product2->uuid,
                    'quantity' => $product2->quantum * 10,
                    'delivery_date' => $expectedDate1,
                    'confirmed_delivery_date' => $expectedDate1
                ]
            ]
        ]);

        $data = [
            'uuid' => $productRequestUuid,
            'product_request_supplier_status_id' => $status,
            'confirmed_date' => $confirmedDate,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('product_requests', $data);

        $this->assertDatabaseHas('product_pre_requests', [
            'user_uuid' => $supplierUser2->uuid,
            'product_request_uuid' => $productRequestUuid,
            'product_uuid' => $product2->uuid,
            'status' => ProductPreRequest::STATUS_NEW
        ]);
    }
}
