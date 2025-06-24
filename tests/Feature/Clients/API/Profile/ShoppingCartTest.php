<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientShoppingList;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\PromoYellowPrice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class ShoppingCartTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Auth::shouldUse('api-clients');
    }

    /**
     *
     */
    public function testIndex()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        $cart = $self->getShoppingCart();

        /** @var Product $product */
        $product = factory(Product::class)->create();
        $cart->add($product->assortment_uuid, 1.5);
        $cart->save();

        /** @var PromoYellowPrice $yellowPrice */
        $yellowPrice = factory(PromoYellowPrice::class)->create([
            'assortment_uuid' => $product->assortment_uuid,
            'is_enabled' => true,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);
        $yellowPrice->stores()->sync([
            $product->user_uuid
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/shopping-cart/assortments?store_uuid=' . $product->user_uuid);

        $json->assertJson([
            'data' => [
                [
                    'assortment' => [
                        'uuid' => $product->assortment_uuid,
                        'price_with_discount' => $yellowPrice->price,
                        'current_price' => $product->price,
                        'discount_type' => PromoYellowPrice::class
                    ],
                    'quantity' => 1.5,
                ]
            ]
        ]);
    }

    /**
     *
     */
    public function testStore()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();

        $quantity = $this->faker->randomFloat(3, 1, 200);
        $json = $this->be($self)->postJson('/clients/api/profile/shopping-cart/assortments', [
            'uuid' => $assortment->uuid,
            'quantity' => $quantity,
        ]);
        $json->assertSuccessful();
        $self->refresh();
        $cart = $self->getShoppingCart();
        $assort = $cart->get($assortment->uuid);
        $this->assertInstanceOf(Assortment::class, $assort);
        $this->assertEquals($quantity, $assort->shopping_cart_quantity);
    }

    /**
     *
     */
    public function testShow()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        $cart = $self->getShoppingCart();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $cart->add($assortment->uuid, 10);
        $cart->save();

        $json = $this->be($self)->getJson('/clients/api/profile/shopping-cart/assortments/' . $assortment->uuid);

        $json->assertJson([
            'data' => [
                'assortment' => [
                    'uuid' => $assortment->uuid,
                ],
                'quantity' => 10
            ]
        ]);
    }

    /**
     *
     */
    public function testCustomUpdate()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();


        $json = $this->be($self)->postJson('/clients/api/profile/shopping-cart/assortments/update', [
            'uuid' => $assortment->uuid,
            'quantity' => 3,
        ]);
        $json->assertSuccessful();
        $self->refresh();
        $cart = $self->getShoppingCart();
        $assort = $cart->get($assortment->uuid);
        $this->assertInstanceOf(Assortment::class, $assort);
        $this->assertEquals(3, $assort->shopping_cart_quantity);
    }

    /**
     *
     */
    public function testDestroy()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        $cart = $self->getShoppingCart();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $cart->add($assortment->uuid, 10);
        $cart->save();

        $json = $this->be($self)->deleteJson('/clients/api/profile/shopping-cart/assortments/' . $assortment->uuid);
        $json->assertSuccessful();

        $self->refresh();
        $cart = $self->getShoppingCart();
        $assort = $cart->get($assortment->uuid);
        $this->assertNull($assort);
    }

    /**
     *
     */
    public function testClear()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        $cart = $self->getShoppingCart();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $cart->add($assortment->uuid, 10);
        $cart->save();

        $json = $this->be($self)->deleteJson('/clients/api/profile/shopping-cart/assortments');
        $json->assertSuccessful();

        $self->refresh();
        $cart = $self->getShoppingCart();
        $assort = $cart->get($assortment->uuid);
        $this->assertNull($assort);
    }

    /**
     *
     */
    public function testFillFromShoppingList()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        $cart = $self->getShoppingCart();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        /** @var Assortment $assortment2 */
        $assortment2 = factory(Assortment::class)->create();
        $cart->add($assortment->uuid, 10);
        $cart->save();

        /** @var ClientShoppingList $list */
        $list = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid
        ]);
        $list->assortments()->sync([
            $assortment->uuid => ['quantity' => 5],
            $assortment2->uuid => ['quantity' => 25],
        ]);

        $json = $this->be($self)->postJson('/clients/api/profile/shopping-cart/fill-from-shopping-list/' . $list->uuid);
        $json->assertSuccessful();

        $self->refresh();
        $cart = $self->getShoppingCart();
        $assort = $cart->get($assortment->uuid);
        $this->assertEquals($assort->shopping_cart_quantity, 5);
        $assort2 = $cart->get($assortment2->uuid);
        $this->assertEquals($assort2->shopping_cart_quantity, 25);
    }

    /**
     *
     */
    public function testFillFromOrder()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        $cart = $self->getShoppingCart();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $cart->add($assortment->uuid, 10);
        $cart->save();

        /** @var Order $order */
        $order = factory(Order::class)->create([
            'client_uuid' => $self->uuid
        ]);
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid,
            'quantity' => 25
        ]);

        $json = $this->be($self)->postJson('/clients/api/profile/shopping-cart/fill-from-order/' . $order->uuid);
        $json->assertSuccessful();

        $self->refresh();
        $cart = $self->getShoppingCart();
        $assort = $cart->get($assortment->uuid);
        $this->assertEquals($assort->shopping_cart_quantity, 10);
        $assort2 = $cart->get($orderProduct->product->assortment_uuid);
        $this->assertEquals($assort2->shopping_cart_quantity, 25);
    }
}
