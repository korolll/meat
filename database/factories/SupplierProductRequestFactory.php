<?php

use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductPreRequest;
use App\Models\ProductRequestDeliveryMethod;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(SupplierProductRequest::class, function (Faker $faker) {
    return [
        'customer_user_uuid' => function () {
            return factory(User::class)->state('store')->create()->uuid;
        },
        'supplier_user_uuid' => function () {
            return factory(User::class)->state('distribution-center')->create()->uuid;
        },
        'delivery_user_uuid' => function () {
            return factory(User::class)->state('delivery-service')->create()->uuid;
        },
        'expected_delivery_date' => $faker->dateTimeBetween('+1 day', '+7 day'),
        'product_request_delivery_method_id' => ProductRequestDeliveryMethod::ID_DELIVERY,
    ];
});

$factory->state(SupplierProductRequest::class, 'confirmed_date', function (Faker $faker) {
    return [
        'product_request_supplier_status_id' => SupplierProductRequest::STATUS_SUITABLE_FOR_CONFIRMED_DATE[random_int
        (0, 2)],
        'confirmed_date' => $faker->dateTimeBetween('0 day', '+30 day'),
    ];
})->afterCreatingState(SupplierProductRequest::class, 'confirmed_date', function (SupplierProductRequest $supplierProductRequest) {
    $expectedDate1 = now()->addDays(10);

    /**
     * @var $product1 Product
     */
    $product1 = factory(Product::class)->create([
        'user_uuid' => $supplierProductRequest->supplierUser->uuid,
        'min_delivery_time' => 1,
        'delivery_weekdays' => [$expectedDate1->dayOfWeek],
        'price' => 10,
        'volume' => 1000,
    ]);
    /**
     * @var $product2 Product
     */
    $product2 = factory(Product::class)->create([
        'user_uuid' => $supplierProductRequest->supplierUser->uuid,
        'min_delivery_time' => 1,
        'delivery_weekdays' => [$expectedDate1->dayOfWeek],
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
        $product1->uuid => ['price_new' => 300],
        $product2->uuid => ['price_new' => 200],
    ]);

    $supplierProductRequest->products()->attach([
        $product1->uuid => [
            'quantity' => $product1->quantity,
            'price' => $product1->price,
            'weight' => $product1->assortment->weight,
            'volume' => $product1->volume,
        ],
        $product2->uuid => [
            'quantity' => $product2->quantity,
            'price' => $product2->price,
            'weight' => $product2->assortment->weight,
            'volume' => $product2->volume,
        ]
    ]);

    /**
     * @var $preRequest1 ProductPreRequest
     */
    factory(ProductPreRequest::class)->create([
        'user_uuid' => $supplierProductRequest->supplierUser->uuid,
        'product_request_uuid' => $supplierProductRequest->uuid,
        'product_uuid' => $product1->uuid,
        'quantity' => $product1->quantum * 10,
        'delivery_date' => now()->addHour(),
        'confirmed_delivery_date' => $expectedDate1,
    ]);
    /**
     * @var $preRequest2 ProductPreRequest
     */
    factory(ProductPreRequest::class)->create([
        'user_uuid' => $supplierProductRequest->supplierUser->uuid,
        'product_request_uuid' => $supplierProductRequest->uuid,
        'product_uuid' => $product2->uuid,
        'quantity' => $product2->quantum * 10,
        'delivery_date' => now()->addMinutes(30),
        'confirmed_delivery_date' => $expectedDate1,
        'status' => 1
    ]);
});
