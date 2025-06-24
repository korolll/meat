<?php


namespace Tests\Feature\API\Profile\ProductRequests;

use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductPreRequest;
use App\Models\ProductRequest;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class ProductPreRequestsCreateSupplierRequestCommandTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @param string $userState
     * @param bool $hasPrivatePriceList
     * @param bool $success
     */
    public function test1()
    {
        $product_price = 300;
        $user = factory(User::class)->state('supplier')->create();
        $self = factory(User::class)->state('store')->create();
        $expectedDate1 = now()->addDays(3);
        $expectedDate2 = now()->addDays(2);
        $expectedDate3 = now()->addDays(4);

        /**
         * @var $product1 Product
         */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => null,
            'volume' => 1000,
        ]);

        /**
         * @var $product2 Product
         */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate2->dayOfWeek],
            'price' => null
        ]);

        /**
         * @var $priceList PriceList
         */
        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);
        $priceList->products()->attach([
            $product1->uuid => ['price_new' => $product_price],
            $product2->uuid => ['price_new' => $product_price],
        ]);

        /**
         * @var $supplierProductRequest1 SupplierProductRequest
         */
        $supplierProductRequest1 = factory(ProductRequest::class)->create([
            'supplier_user_uuid' => $self->uuid,
        ]);
        /**
         * @var $supplierProductRequest2 SupplierProductRequest
         */
        $supplierProductRequest2 = factory(ProductRequest::class)->create([
            'supplier_user_uuid' => $self->uuid,
        ]);

        $preRequest1 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $self->uuid,
            'product_request_uuid' => $supplierProductRequest1->uuid,
            'product_uuid' => $product1->uuid,
            'quantity' => $product1->quantum * 10,
            'delivery_date' => now()->subHour(),
            'confirmed_delivery_date' => $expectedDate1,
        ]);

        $preRequest2 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $self->uuid,
            'product_request_uuid' => $supplierProductRequest1->uuid,
            'product_uuid' => $product2->uuid,
            'quantity' => $product2->quantum * 10,
            'delivery_date' => now()->subHour(),
            'confirmed_delivery_date' => $expectedDate2,
        ]);

        $preRequest3 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $self->uuid,
            'product_request_uuid' => $supplierProductRequest2->uuid,
            'product_uuid' => $product2->uuid,
            'quantity' => $product2->quantum * 12,
            'delivery_date' => now()->subHour(),
            'confirmed_delivery_date' => $expectedDate2,
        ]);

        $this->artisan('product-pre-request:create-supplier-request');

        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest1->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);

        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest2->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);

        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest3->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);

//        $this->getConnection()
//            ->table('product_requests')
//            ->where([
//                'customer_user_uuid' => ,
//                'supplier_user_uuid' => ,
//            ]);

//        $this->assertDatabaseHas('customer_product_request_supplier_product_request', [
//            'customer_product_request_uuid' => $preRequest3->id,
//            'supplier_product_request_uuid' => ProductPreRequest::STATUS_DONE
//        ]);

        $this->assertDatabaseHas('product_pre_request_customer_supplier_relation', [
            'customer_user_uuid' => $self->uuid,
            'supplier_user_uuid' => $user->uuid
        ]);
    }

    /**
     * @param string $userState
     * @param bool $hasPrivatePriceList
     * @param bool $success
     */
    public function test2()
    {
        $product_price = 300;
        /**
         * @var $user1 User
         */
        $user1 = factory(User::class)->state('supplier')->create();
        /**
         * @var $user2 User
         */
        $user2 = factory(User::class)->state('supplier')->create();
        /**
         * @var $user3 User
         */
        $user3 = factory(User::class)->state('supplier')->create();
        /**
         * @var $self User
         */
        $self = factory(User::class)->state('store')->create();

        $expectedDate1 = now()->addDays(10);
        $expectedDate2 = now()->addDays(2);

        /**
         * @var $product1 Product
         */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $user1->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => 10,
            'volume' => 1000,
        ]);
        /**
         * @var $product2 Product
         */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $user2->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => 20
        ]);
        /**
         * @var $product3 Product
         */
        $product3 = factory(Product::class)->create([
            'user_uuid' => $user2->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => 20
        ]);

        /**
         * @var $priceList PriceList
         */
        $priceList1 = factory(PriceList::class)->create([
            'user_uuid' => $user1->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);
        /**
         * @var $priceList PriceList
         */
        $priceList2 = factory(PriceList::class)->create([
            'user_uuid' => $user2->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);

        $priceList1->products()->attach([
            $product1->uuid => ['price_new' => $product_price],
        ]);
        $priceList2->products()->attach([
            $product2->uuid => ['price_new' => $product_price],
        ]);

        /**
         * @var $supplierProductRequest1 SupplierProductRequest
         */
        $supplierProductRequest1 = factory(ProductRequest::class)->create([
            'customer_user_uuid' => $self->uuid,
            'supplier_user_uuid' => $user1->uuid,
        ]);
        $supplierProductRequest1->products()->attach([
            $product1->uuid => [
                'quantity' => $product1->quantity,
                'price' => $product1->price,
                'weight' => $product1->assortment->weight,
                'volume' => $product1->volume,
            ]
        ]);
        /**
         * @var $supplierProductRequest2 SupplierProductRequest
         */
        $supplierProductRequest2 = factory(ProductRequest::class)->create([
            'customer_user_uuid' => $self->uuid,
            'supplier_user_uuid' => $user2->uuid,
        ]);
        $supplierProductRequest2->products()->attach([
            $product2->uuid => [
                'quantity' => $product2->quantity,
                'price' => $product2->price,
                'weight' => $product2->assortment->weight,
                'volume' => $product2->volume,
            ]
        ]);
        /**
         * @var $supplierProductRequest3 SupplierProductRequest
         */
        $supplierProductRequest3 = factory(ProductRequest::class)->create([
            'customer_user_uuid' => $self->uuid,
            'supplier_user_uuid' => $user2->uuid,
        ]);
        $supplierProductRequest3->products()->attach([
            $product3->uuid => [
                'quantity' => $product3->quantity,
                'price' => $product3->price,
                'weight' => $product3->assortment->weight,
                'volume' => $product3->volume,
            ]
        ]);
        /**
         * @var $supplierProductRequest4 SupplierProductRequest
         */
        $supplierProductRequest4 = factory(ProductRequest::class)->create([
            'customer_user_uuid' => $self->uuid,
            'supplier_user_uuid' => $user2->uuid,
        ]);
        $supplierProductRequest4->products()->attach([
            $product3->uuid => [
                'quantity' => $product3->quantity,
                'price' => $product3->price,
                'weight' => $product3->assortment->weight,
                'volume' => $product3->volume,
            ]
        ]);

        /**
         * @var $preRequest1 ProductPreRequest
         */
        $preRequest1 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user3->uuid,
            'product_request_uuid' => $supplierProductRequest1->uuid,
            'product_uuid' => $product1->uuid,
            'quantity' => $product1->quantum * 10,
            'delivery_date' => now()->subHour(),
            'confirmed_delivery_date' => $expectedDate1,
        ]);
        /**
         * @var $preRequest2 ProductPreRequest
         */
        $preRequest2 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user3->uuid,
            'product_request_uuid' => $supplierProductRequest2->uuid,
            'product_uuid' => $product2->uuid,
            'quantity' => $product2->quantum * 10,
            'delivery_date' => now()->subMinutes(30),
            'confirmed_delivery_date' => $expectedDate1,
        ]);
        /**
         * @var $preRequest2 ProductPreRequest
         */
        $preRequest3 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user3->uuid,
            'product_request_uuid' => $supplierProductRequest2->uuid,
            'product_uuid' => $product3->uuid,
            'quantity' => $product3->quantum * 10,
            'delivery_date' => now()->subMinutes(30),
            'confirmed_delivery_date' => $expectedDate1,
        ]);
        /**
         * @var $preRequest2 ProductPreRequest
         */
        $preRequest4 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user3->uuid,
            'product_request_uuid' => $supplierProductRequest3->uuid,
            'product_uuid' => $product3->uuid,
            'quantity' => $product3->quantum * 20,
            'delivery_date' => now()->subMinutes(30),
            'confirmed_delivery_date' => $expectedDate1,
        ]);

        $this->artisan('product-pre-request:create-supplier-request');

        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest1->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);
        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest2->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);
        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest3->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);
        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest4->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);

//        $this->getConnection()
//            ->table('product_requests')
//            ->where([
//                'customer_user_uuid' => ,
//                'supplier_user_uuid' => ,
//            ]);

//        $this->assertDatabaseHas('customer_product_request_supplier_product_request', [
//            'customer_product_request_uuid' => $preRequest3->id,
//            'supplier_product_request_uuid' => ProductPreRequest::STATUS_DONE
//        ]);

//        $this->assertDatabaseHas('product_pre_request_customer_supplier_relation', [
//            'customer_user_uuid' => $self->uuid,
//            'supplier_user_uuid' => $user->uuid
//        ]);
    }

    /**
     * @param string $userState
     * @param bool $hasPrivatePriceList
     * @param bool $success
     */
    public function test3()
    {
        $product_price = 300;
        /**
         * @var $user1 User
         */
        $user1 = factory(User::class)->state('supplier')->create();
        /**
         * @var $user2 User
         */
        $user2 = factory(User::class)->state('supplier')->create();
        /**
         * @var $user3 User
         */
        $user3 = factory(User::class)->state('supplier')->create();
        /**
         * @var $self User
         */
        $self = factory(User::class)->state('store')->create();

        $expectedDate1 = now()->addDays(10);
        $expectedDate2 = now()->addDays(2);

        /**
         * @var $product1 Product
         */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $user1->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => 10,
            'volume' => 1000,
        ]);
        /**
         * @var $product2 Product
         */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $user2->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => 20
        ]);
        /**
         * @var $product3 Product
         */
        $product3 = factory(Product::class)->create([
            'user_uuid' => $user2->uuid,
            'min_delivery_time' => 1,
            'delivery_weekdays' => [$expectedDate1->dayOfWeek],
            'price' => 30
        ]);

        /**
         * @var $priceList PriceList
         */
        $priceList1 = factory(PriceList::class)->create([
            'user_uuid' => $user1->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);
        /**
         * @var $priceList PriceList
         */
        $priceList2 = factory(PriceList::class)->create([
            'user_uuid' => $user2->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);

        $priceList1->products()->attach([
            $product1->uuid => ['price_new' => $product_price],
        ]);
        $priceList2->products()->attach([
            $product2->uuid => ['price_new' => $product_price],
        ]);

        /**
         * @var $supplierProductRequest1 SupplierProductRequest
         */
        $supplierProductRequest1 = factory(ProductRequest::class)->create([
            'customer_user_uuid' => $self->uuid,
            'supplier_user_uuid' => $user1->uuid,
        ]);
        $supplierProductRequest1->products()->attach([
            $product1->uuid => [
                'quantity' => $product1->quantity,
                'price' => $product1->price,
                'weight' => $product1->assortment->weight,
                'volume' => $product1->volume,
            ]
        ]);
        /**
         * @var $supplierProductRequest2 SupplierProductRequest
         */
        $supplierProductRequest2 = factory(ProductRequest::class)->create([
            'customer_user_uuid' => $self->uuid,
            'supplier_user_uuid' => $user2->uuid,
        ]);
        $supplierProductRequest2->products()->attach([
            $product2->uuid => [
                'quantity' => $product2->quantity,
                'price' => $product2->price,
                'weight' => $product2->assortment->weight,
                'volume' => $product2->volume,
            ]
        ]);
        /**
         * @var $supplierProductRequest3 SupplierProductRequest
         */
        $supplierProductRequest3 = factory(ProductRequest::class)->create([
            'customer_user_uuid' => $self->uuid,
            'supplier_user_uuid' => $user2->uuid,
        ]);
        $supplierProductRequest3->products()->attach([
            $product3->uuid => [
                'quantity' => $product3->quantity,
                'price' => $product3->price,
                'weight' => $product3->assortment->weight,
                'volume' => $product3->volume,
            ]
        ]);
        /**
         * @var $supplierProductRequest4 SupplierProductRequest
         */
        $supplierProductRequest4 = factory(ProductRequest::class)->create([
            'customer_user_uuid' => $self->uuid,
            'supplier_user_uuid' => $user2->uuid,
        ]);
        $supplierProductRequest4->products()->attach([
            $product3->uuid => [
                'quantity' => $product3->quantity,
                'price' => $product3->price,
                'weight' => $product3->assortment->weight,
                'volume' => $product3->volume,
            ]
        ]);

        /**
         * @var $preRequest1 ProductPreRequest
         */
        $preRequest1 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user3->uuid,
            'product_request_uuid' => $supplierProductRequest1->uuid,
            'product_uuid' => $product1->uuid,
            'quantity' => $product1->quantum * 10,
            'delivery_date' => now()->addHours(2),
            'confirmed_delivery_date' => $expectedDate1,
        ]);
        /**
         * @var $preRequest2 ProductPreRequest
         */
        $preRequest2 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user2->uuid,
            'product_request_uuid' => $supplierProductRequest2->uuid,
            'product_uuid' => $product2->uuid,
            'quantity' => $product2->quantum * 10,
            'delivery_date' => now()->addMinutes(30),
            'confirmed_delivery_date' => $expectedDate1,
        ]);
        /**
         * @var $preRequest2 ProductPreRequest
         */
        $preRequest3 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user2->uuid,
            'product_request_uuid' => $supplierProductRequest2->uuid,
            'product_uuid' => $product3->uuid,
            'quantity' => $product3->quantum * 10,
            'delivery_date' => now()->subMinutes(30),
            'confirmed_delivery_date' => $expectedDate1,
        ]);
        /**
         * @var $preRequest2 ProductPreRequest
         */
        $preRequest4 = factory(ProductPreRequest::class)->create([
            'user_uuid' => $user2->uuid,
            'product_request_uuid' => $supplierProductRequest3->uuid,
            'product_uuid' => $product3->uuid,
            'quantity' => $product3->quantum * 20,
            'delivery_date' => now()->subMinutes(30),
            'confirmed_delivery_date' => $expectedDate1,
        ]);

        $this->artisan('product-pre-request:create-supplier-request');

        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest1->id,
            'status' => ProductPreRequest::STATUS_NEW
        ]);
        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest2->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);
        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest3->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);
        $this->assertDatabaseHas('product_pre_requests', [
            'id' => $preRequest4->id,
            'status' => ProductPreRequest::STATUS_DONE
        ]);

//        $this->getConnection()
//            ->table('product_requests')
//            ->where([
//                'customer_user_uuid' => ,
//                'supplier_user_uuid' => ,
//            ]);

//        $this->assertDatabaseHas('customer_product_request_supplier_product_request', [
//            'customer_product_request_uuid' => $preRequest3->id,
//            'supplier_product_request_uuid' => ProductPreRequest::STATUS_DONE
//        ]);

//        $this->assertDatabaseHas('product_pre_request_customer_supplier_relation', [
//            'customer_user_uuid' => $self->uuid,
//            'supplier_user_uuid' => $user->uuid
//        ]);
    }
}
