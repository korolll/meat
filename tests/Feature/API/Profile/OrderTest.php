<?php

namespace Tests\Feature\API\Profile;

use App\Models\Order;
use App\Models\OrderPaymentType;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCaseNotificationsFake;

class OrderTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex()
    {
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'store_user_uuid' => $self->uuid
        ]);
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid
        ]);

        $json = $this->be($self)->getJson('/api/profile/orders');
        $json->assertSuccessful()->assertJson([
            'data' => [[
                'uuid' => $orderProduct->order_uuid,
            ]]
        ]);
    }

    /**
     * @test
     */
    public function testShow()
    {
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'store_user_uuid' => $self->uuid
        ]);
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid
        ]);

        $json = $this->be($self)->getJson("/api/profile/orders/{$order->uuid}");
        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $orderProduct->order_uuid,
            ],
        ]);
    }

    /**
     * @param string $current
     * @param string $new
     * @param false  $shouldBeAnError
     *
     * @dataProvider setStatusDataProvider
     */
    public function testSetStatus(string $current, string $new, bool $shouldBeAnError = false)
    {
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_payment_type_id' => OrderPaymentType::ID_CASH,
            'store_user_uuid' => $self->uuid,
            'order_status_id' => $current
        ]);

        $data = [
            'order_status_id' => $new
        ];

        $json = $this->be($self)->postJson("/api/profile/orders/$order->uuid/set-status", $data);
        if ($shouldBeAnError) {
            $json->assertStatus(Response::HTTP_BAD_REQUEST);
        } else {
            $json->assertSuccessful();
        }

        $order->refresh();
        $this->assertEquals($order->order_status_id, $shouldBeAnError ? $current : $new);
    }

    /**
     * @return array[]
     */
    public function setStatusDataProvider(): array
    {
        return [
            [OrderStatus::ID_COLLECTING, OrderStatus::ID_COLLECTED],
            [OrderStatus::ID_COLLECTED, OrderStatus::ID_DELIVERING],
            [OrderStatus::ID_DONE, OrderStatus::ID_CANCELLED, true],
            [OrderStatus::ID_NEW, OrderStatus::ID_CANCELLED, true]
        ];
    }
}
