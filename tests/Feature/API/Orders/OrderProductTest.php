<?php

namespace Tests\Feature\API\Orders;

use App\Jobs\SendOrderToIikoJob;
use App\Models\Assortment;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderDeliveryType;
use App\Models\OrderPaymentType;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\Management\Client\Order\OrderFactoryInterface;
use App\Services\Money\MoneyHelper;
use App\Services\Quantity\FloatHelper;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCaseNotificationsFake;

class OrderProductTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testShow()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create();

        $json = $this->be($self)->getJson("/api/orders/products/{$orderProduct->uuid}");
        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $orderProduct->uuid,
            ],
        ]);
    }

    /**
     *
     */
    public function testStore()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'total_bonus' => 10,
            'paid_bonus' => 5
        ]);
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->make([
            'order_uuid' => $order->uuid
        ]);

        $data = [
            'order_uuid' => $orderProduct->order_uuid,
            'assortment_uuid' => $orderProduct->product->assortment_uuid,
            'quantity' => $this->faker->randomFloat(2, 1, 10),
        ];

        $order = $orderProduct->order;
        $oldData = $order->getCollectionPriceData()->toArray();

        $product = $orderProduct->product;
        $json = $this->be($self)->postJson("/api/orders/products", $data);
        $json->assertSuccessful();

        $data['product_uuid'] = $orderProduct->product_uuid;
        unset($data['assortment_uuid']);
        $this->assertDatabaseHas('order_products', $data);

        $order->refresh();
        $orderProduct->refresh();

        $total = MoneyHelper::of($orderProduct->product->price)->multipliedBy($data['quantity']);
        $total = MoneyHelper::toFloat($total);

        // Check price for product
        $this->assertEquals($total, $order->total_price_for_products_with_discount);

        // Check that all have changed in order
        $newData = $order->getCollectionPriceData()->toArray();
        unset($oldData['total_discount']); // ignore discount
        unset($oldData['total_bonus']); // ignore bonus
        unset($oldData['paid_bonus']); // ignore bonus
        foreach ($oldData as $key => $value) {
            $this->assertNotEquals($value, $newData[$key]);
        }

        // Check that all have changed in order product
        $newDataProduct = $orderProduct->getPriceData()->toArray();
        $this->assertNotEquals(0, $newDataProduct['total_amount_with_discount']);
        $this->assertNotEquals(0, $newDataProduct['total_weight']);
        $this->assertNotEquals(0, $newDataProduct['total_quantity']);

        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $product->quantity,
            'quantity_delta' => $data['quantity']
        ]);
    }

    /**
     *
     */
    public function testStoreExist()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create();

        $data = [
            'order_uuid' => $orderProduct->order_uuid,
            'assortment_uuid' => $orderProduct->product->assortment_uuid,
            'quantity' => $this->faker->randomFloat(2, 1, 10),
        ];

        $json = $this->be($self)->postJson("/api/orders/products", $data);
        $json->assertStatus(Response::HTTP_BAD_REQUEST);
        $json->assertJson([
            'message' => 'Product is already exist in the order'
        ]);
    }

    /**
     * @testWith [1, 5.11111]
     *           [5, 20]
     *           [1, 99]
     *           [5.111, 1]
     *           [300, 1]
     *           [10, 0]
     */
    public function testUpdate($oldQuantity, $newQuantity)
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();

        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'bonus_percent' => 50
        ]);
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'user_uuid' => $store->uuid,
            'assortment_uuid' => $assortment->uuid,
            'price' => $this->faker->randomFloat(2, 100, 250)
        ]);

        /** @var OrderFactoryInterface $factory */
        $factory = app(OrderFactoryInterface::class);
        $paidBonus = $this->faker->numberBetween(1, 10);

        $attrs = [
            'store_user_uuid' => $store->uuid,
            'order_delivery_type_id' => OrderDeliveryType::ID_PICKUP,
            'order_payment_type_id' => OrderPaymentType::ID_CASH,

            'client_email' => $this->faker->email,

            'paid_bonus' => $paidBonus,

            'client_address_data' => [
                'address' => $this->faker->address,
                'floor' => $this->faker->numberBetween(1, 10),
                'entrance' => $this->faker->numberBetween(1, 10),
                'apartment_number' => $this->faker->numberBetween(1, 10),
                'intercom_code' => $this->faker->numerify('#####'),
            ],

            'planned_delivery_datetime_from' => $this->faker->dateTime,
            'planned_delivery_datetime_to' => $this->faker->dateTime
        ];

        $order = $factory->create($client, $attrs, [[
            'assortment_uuid' => $product->assortment_uuid,
            'quantity' => $oldQuantity
        ]]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = $order->orderProducts->first();

        $data = [
            'quantity' => $newQuantity,
        ];

        $json = $this->be($self)->putJson("/api/orders/products/$orderProduct->uuid", $data);
        $newQuantity = FloatHelper::round($newQuantity);
        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $orderProduct->uuid,
                'quantity' => $newQuantity,
            ],
        ]);

        $this->assertDatabaseHas('order_products', [
            'uuid' => $orderProduct->uuid,
            'quantity' => $newQuantity,
        ]);

        $oldData = $order->getCollectionPriceData()->toArray();
        $oldDataProduct = $orderProduct->getPriceData()->toArray();

        $order->refresh();
        $orderProduct->refresh();
        $paidBonusNew = $orderProduct->getPriceData()->getPaidBonus();

        $price = MoneyHelper::of($orderProduct->product->price);
        $total = MoneyHelper::toFloat($price->multipliedBy($newQuantity)->minus($paidBonusNew));
        // Check price for product
        $this->assertEquals($total, $order->total_price_for_products_with_discount);

        // Test SendOrderToIikoJob
//        SendOrderToIikoJob::dispatchSync($order);

        // Check that all have changed in order
        $newData = $order->getCollectionPriceData()->toArray();
        unset($oldData['total_discount']);
        foreach ($oldData as $key => $value) {
            $this->assertNotEquals($value, $newData[$key]);
        }

        // Check that all have changed in order product
        $newDataProduct = $orderProduct->getPriceData()->toArray();
        $this->assertNotEquals($oldDataProduct['total_amount_with_discount'], $newDataProduct['total_amount_with_discount']);
        $this->assertNotEquals($oldDataProduct['total_weight'], $newDataProduct['total_weight']);
        $this->assertNotEquals($oldDataProduct['total_quantity'], $newDataProduct['total_quantity']);

        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $product->quantity,
            'quantity_delta' => -$oldQuantity
        ]);

        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $product->quantity - $oldQuantity,
            'quantity_delta' => $newQuantity - $oldQuantity
        ]);
    }
}
