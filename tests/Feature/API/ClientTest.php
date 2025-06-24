<?php

namespace Feature\API;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Ramsey\Uuid\Uuid;
use Tests\TestCaseNotificationsFake;

class ClientTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     * @testWith [false]
     *           [true]
     */
    public function testIndexHasFavorite(bool $has)
    {
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();
        $reqData = [
            'where' => [[
                'has_favorites',
                '=',
                (int)$has
            ]]
        ];

        /** @var Client $client */
        $client = factory(Client::class)->create();
        if ($has) {
            /** @var Assortment $assortment */
            $assortment = factory(Assortment::class)->create();
            $client->favoriteAssortments()->sync([
                $assortment->uuid
            ]);
        }

        $json = $this->be($self)->json('GET', '/api/clients', $reqData);
        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $client->uuid,
        ]);
    }

    /**
     * @test
     * @testWith [false]
     *           [true]
     */
    public function testIndexHasGoodsInShoppingCart(bool $has)
    {
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();
        $reqData = [
            'where' => [[
                'has_goods_in_shopping_cart',
                '=',
                (int)$has
            ]]
        ];

        /** @var Client $client */
        $client = factory(Client::class)->create();
        $client->getDateFormat();

        if ($has) {
            $cart = [
                'data' => [Uuid::uuid4()->toString()],
                'updated_at' => now()->subDays(3)->format($client->getDateFormat())
            ];
        } else {
            $cart = [
                'data' => [],
                'updated_at' => now()->format($client->getDateFormat())
            ];
        }

        $client->shopping_cart_data = $cart;
        $client->save();

        $json = $this->be($self)->json('GET', '/api/clients', $reqData);
        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $client->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testIndexPurchases()
    {
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();

        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var Client $client */
        factory(Client::class)->create();

        /** @var Order $order */
        $order = factory(Order::class)->create([
            'client_uuid' => $client->uuid,
            'order_status_id' => OrderStatus::ID_DONE
        ]);
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid
        ]);
        $catalogUuid = $orderProduct->product->assortment->catalog_uuid;
        $purchasedAt = $order->planned_delivery_datetime_from;
        $from = $purchasedAt->clone()->subDay();
        $to = $purchasedAt->clone()->addDay();

        $reqData = [
            'where' => [
                [
                    'purchase_catalog_uuid',
                    '=',
                    $catalogUuid
                ],
                [
                    'purchase_date',
                    'between',
                    [$from, $to]
                ],
            ]
        ];

        $json = $this->be($self)->json('GET', '/api/clients', $reqData);
        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $client->uuid,
        ]);
    }
}
