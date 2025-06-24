<?php

namespace Tests\Feature\API\Profile\ProductRequests;

use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductPreRequest;
use App\Models\ProductRequest;
use App\Models\ProductRequestCustomerStatus;
use App\Models\ProductRequestDeliveryMethod;
use App\Models\ProductRequests\CustomerProductRequest;
use App\Models\RatingType;
use App\Models\User;
use App\Notifications\API\CustomerProductRequestStatusCanceled;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCaseNotificationsFake;

class CustomerProductRequestTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $request = factory(ProductRequest::class)->create();

        $self = $request->customerUser;
        $json = $this->be($self)->getJson('/api/profile/product-requests/customer');

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
     * @return array
     */
    public function storeDataProvider()
    {
        return [
            ['supplier', true, true],
            ['supplier', false, true],
            ['store', false, false]
        ];
    }

    /**
     * @param string $userState
     * @param bool $hasPrivatePriceList
     * @param bool $success
     *
     * @test
     * @dataProvider storeDataProvider
     */
    public function store($userState, $hasPrivatePriceList, $success)
    {
        $product_price = 300;
        $user = factory(User::class)->state($userState)->create();
        $self = factory(User::class)->state('store')->create();
        $expectedDate1 = now()->addDays(1);
        $expectedDate2 = now()->addDays(2);

        $product1 = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => null,
            'volume' => 1000,
        ]);

        $product2 = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate2->dayOfWeek],
            'price' => null
        ]);

        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => $hasPrivatePriceList ? $self->uuid : null
        ]);
        $priceList->products()->attach([
            $product1->uuid => ['price_new' => $product_price],
            $product2->uuid => ['price_new' => $product_price],
        ]);

        $supplierProductRequest = factory(ProductRequest::class)->create([
            'supplier_user_uuid' => $self->uuid,
        ]);

        $preRequest1 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user->uuid,
            'product_request_uuid' => $supplierProductRequest->uuid,
            'product_uuid' => $product1->uuid,
            'quantity' => $product1->quantum * 10,
            'delivery_date' => $expectedDate1,
            'confirmed_delivery_date' => $expectedDate1,
        ]);

        $preRequest2 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user->uuid,
            'product_request_uuid' => $supplierProductRequest->uuid,
            'product_uuid' => $product2->uuid,
            'quantity' => $product2->quantum * 10,
            'delivery_date' => $expectedDate2,
            'confirmed_delivery_date' => $expectedDate2,
        ]);

        $json = $this->be($self)->postJson('/api/profile/product-requests/customer', [
            'supplier_product_requests' => [
                [
                    'uuid' => $supplierProductRequest->uuid,
                ]
            ],
            'products' => [
                [
                    'product_uuid' => $product1->uuid,
                    'quantity' => $product1->min_quantity_in_order,
                    'expected_delivery_date' => $expectedDate1,
                ],
                [
                    'product_uuid' => $product2->uuid,
                    'quantity' => $product2->min_quantity_in_order,
                    'expected_delivery_date' => $expectedDate2,
                ],
            ],
            'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_DELIVERY,
        ]);

        if (!$success) {
            $json->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

            return;
        }

        $json->assertSuccessful();

        $this->assertDatabaseHas('product_requests', [
            'customer_user_uuid' => $self->uuid,
            'expected_delivery_date' => $expectedDate1,
            'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_DELIVERY,
            'price' => $product_price * $product1->min_quantity_in_order,
            'volume' => $product1->volume * $product1->min_quantity_in_order,
        ]);

        $this->assertDatabaseHas('product_requests', [
            'customer_user_uuid' => $self->uuid,
            'expected_delivery_date' => $expectedDate2,
            'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_DELIVERY
        ]);

        $this->assertDatabaseHas('product_product_request', [
            'product_uuid' => $product1->uuid,
            'quantity' => $product1->min_quantity_in_order,
            'quantity_actual' => $product1->min_quantity_in_order,
            'price' => $product_price
        ]);

        $this->assertDatabaseHas('customer_product_request_supplier_product_request', [
            'supplier_product_request_uuid' => $supplierProductRequest->uuid,
        ]);

        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest1->id,
            'status' => ProductPreRequest::STATUS_HAND_PRODUCT_REQUEST
        ]);

        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest2->id,
            'status' => ProductPreRequest::STATUS_HAND_PRODUCT_REQUEST
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $request = factory(ProductRequest::class)->create();

        $self = $request->customerUser;
        $json = $this->be($self)->getJson("/api/profile/product-requests/customer/{$request->uuid}");

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
    public function updateProduct()
    {
        $request = factory(ProductRequest::class)->create();
        $product = factory(Product::class)->create(['user_uuid' => $request->customer_user_uuid]);

        $quantity = 19;
        $request->products()->attach($product, [
            'quantity' => $quantity,
            'quantity_actual' => $quantity,
            'price' => $product->price
        ]);

        $self = $request->customerUser;
        $json = $this->be($self)->putJson("/api/profile/product-requests/customer/{$request->uuid}/products/{$product->uuid}", [
            'quantity_actual' => 0,
        ]);

        $json->assertSuccessful();

        $this->assertDatabaseHas('product_product_request', [
            'product_uuid' => $product->uuid,
            'quantity' => $quantity,
            'quantity_actual' => 0,
        ]);
    }

    /**
     * @test
     */
    public function updateNotExistsProduct()
    {
        $request = factory(ProductRequest::class)->create();
        $product = factory(Product::class)->create(['user_uuid' => $request->supplier_user_uuid]);

        $quantity = 19;
        $self = $request->customerUser;
        $json = $this->be($self)->putJson("/api/profile/product-requests/customer/{$request->uuid}/products/{$product->uuid}", [
            'quantity_actual' => $quantity,
        ]);

        $json->assertSuccessful();

        $this->assertDatabaseHas('product_product_request', [
            'product_uuid' => $product->uuid,
            'quantity' => 0,
            'quantity_actual' => $quantity,
            'is_added_product' => true
        ]);
    }

    /**
     * @test
     */
    public function products()
    {
        $request = factory(ProductRequest::class)->create();
        $product = factory(Product::class)->create();

        $quantity = 44;
        $request->products()->attach($product, [
            'quantity' => $quantity,
            'quantity_actual' => $quantity,
            'price' => $product->price
        ]);

        $self = $request->customerUser;
        $json = $this->be($self)->getJson("/api/profile/product-requests/customer/{$request->uuid}/products");

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
     * @param int $quantityInRequest
     *
     * @test
     * @dataProvider setStatusProvider
     */
    public function setStatus($quantityInRequest)
    {
        $actualQuantityInRequest = max(0, $quantityInRequest - 2);

        $request = factory(ProductRequest::class)->create([
            'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_ON_THE_WAY,
        ]);

        $product = factory(Product::class)->create([
            'user_uuid' => $request->supplier_user_uuid,
        ]);

        $request->products()->attach($product, [
            'quantity' => $quantityInRequest,
            'quantity_actual' => $quantityInRequest,
            'price' => $product->price,
        ]);

        $self = $request->customerUser;

        $json = $this->be($self)->putJson("/api/profile/product-requests/customer/{$request->uuid}/products/{$product->uuid}", [
            'quantity_actual' => $actualQuantityInRequest,
        ]);
        $json->assertSuccessful();

        $json = $this->be($self)->putJson("/api/profile/product-requests/customer/{$request->uuid}/set-status", [
            'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_DONE,
            'supplier_rating' => 5,
            'customer_comment' => 'test_comment'
        ]);

        $data = [
            'uuid' => $request->uuid,
            'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_DONE,
            'customer_comment' => 'test_comment',
            'is_partial_delivery' => true,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('product_requests', $data);

        $this->assertDatabaseHas('warehouse_transactions', [
            'reference_id' => $request->uuid,
            'quantity_old' => 0,
            'quantity_delta' => $actualQuantityInRequest,
            'quantity_new' => $actualQuantityInRequest,
        ]);

        $this->assertDatabaseHas('products', [
            'user_uuid' => $request->customer_user_uuid,
            'quantity' => $actualQuantityInRequest,
        ]);

        $this->assertDatabaseHas('catalogs', [
            'user_uuid' => $request->customer_user_uuid,
            'name' => config('app.catalogs.default_catalog_name'),
        ]);

        $this->assertDatabaseHas('rating_scores', [
            'rated_reference_type' => User::MORPH_TYPE_ALIAS,
            'rated_reference_id' => $request->supplier_user_uuid,
            'rated_by_reference_type' => User::MORPH_TYPE_ALIAS,
            'rated_by_reference_id' => $request->customer_user_uuid,
            'rated_through_reference_type' => CustomerProductRequest::MORPH_TYPE_ALIAS,
            'rated_through_reference_id' => $request->uuid,
            'value' => 5,
        ]);

        $this->assertDatabaseHas('ratings', [
            'reference_type' => User::MORPH_TYPE_ALIAS,
            'reference_id' => $request->supplier_user_uuid,
            'rating_type_id' => RatingType::ID_SUPPLIER,
            'value' => 5,
        ]);
    }

    /**
     * @param int $quantityInRequest
     *
     * @test
     * @dataProvider setStatusProvider
     */
    public function setStatusWithDate($quantityInRequest)
    {
        $expected_date = now()->addHours(2);

        $request = factory(ProductRequest::class)->create([
            'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_MATCHING,
        ]);
        $product = factory(Product::class)->create([
            'user_uuid' => $request->supplier_user_uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expected_date->dayOfWeek]
        ]);
        $request->products()->attach($product, [
            'quantity' => $quantityInRequest,
            'quantity_actual' => $quantityInRequest,
            'price' => $product->price
        ]);

        $self = $request->customerUser;
        $json = $this->be($self)->putJson("/api/profile/product-requests/customer/{$request->uuid}/set-status", [
            'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_NEW,
            'expected_delivery_date' => $expected_date,
        ]);

        $data = [
            'uuid' => $request->uuid,
            'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_NEW,
            'expected_delivery_date' => $expected_date,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('product_requests', $data);
    }

    /**
     * @param null|string $comment
     * @param bool $assertSuccess
     * @test
     * @testWith [null, false]
     *           ["test", true]
     */
    public function setStatusCanceledAndCheckNotification(?string $comment, bool $assertSuccess)
    {
        $request = factory(ProductRequest::class)->create([
            'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_NEW,
        ]);
        $product = factory(Product::class)->create([
            'user_uuid' => $request->supplier_user_uuid,
            'min_delivery_time' => 1,
        ]);

        $quantity = 10;
        $request->products()->attach($product, [
            'quantity' => $quantity,
            'quantity_actual' => $quantity,
            'price' => $product->price
        ]);

        $self = $request->customerUser;
        $json = $this->be($self)->putJson("/api/profile/product-requests/customer/{$request->uuid}/set-status", [
            'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_USER_CANCELED,
            'customer_comment' => $comment
        ]);

        if (!$assertSuccess) {
            $json->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

            return;
        }

        Notification::assertSentTo($request->supplierUser, CustomerProductRequestStatusCanceled::class);
        $data = [
            'uuid' => $request->uuid,
            'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_USER_CANCELED,
            'customer_comment' => $comment
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('product_requests', $data);
    }

    /**
     * Данные: [quantityInRequest]
     *
     * @return array
     */
    public function setStatusProvider()
    {
        return [
            [5],
        ];
    }

    /**
     * @test
     */
    public function export()
    {
        Excel::fake();

        $request = factory(ProductRequest::class)->create();

        $self = $request->customerUser;
        $json = $this->be($self)->getJson("/api/profile/product-requests/customer/{$request->uuid}/export/xlsx");

        $json->assertSuccessful();

        Excel::assertDownloaded('customer_request.xlsx');
    }
}
