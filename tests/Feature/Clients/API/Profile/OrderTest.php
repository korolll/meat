<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientBonusTransaction;
use App\Models\ClientCreditCard;
use App\Models\Order;
use App\Models\OrderDeliveryType;
use App\Models\OrderPaymentType;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\PaymentVendorSetting;
use App\Models\Product;
use App\Models\PromoDiverseFoodClientStat;
use App\Models\PromoDiverseFoodClientStatAssortment;
use App\Models\RatingScore;
use App\Models\RatingType;
use App\Models\SystemOrderSetting;
use App\Models\User;
use App\Services\Management\Client\Order\Payment\PaymentStatusEnum;
use App\Services\Management\Rating\OrderProductRatingScoreFactory;
use App\Services\Money\Acquire\AcquireInterface;
use App\Services\Money\Acquire\Data\CreatedPaymentDto;
use App\Services\Money\Acquire\Data\PaymentStatusDto;
use App\Services\Money\Acquire\Resolver\AcquireResolverInterface;
use App\Services\Money\MoneyHelper;
use App\Services\Quantity\FloatHelper;
use Carbon\CarbonImmutable;
use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Model\Address;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Coordinates;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCaseNotificationsFake;

class OrderTest extends TestCaseNotificationsFake
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
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'client_uuid' => $self->uuid
        ]);
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/orders');
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
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'client_uuid' => $self->uuid
        ]);
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid
        ]);

        /** @var OrderProductRatingScoreFactory $ratingScoreFactory */
        $ratingScoreFactory = app('factory.rating-score.order-product');
        $ratingScoreFactory->create($orderProduct->product->assortment, $self, $orderProduct, 5, [
            'comment' => 'test',
        ]);

        $json = $this->be($self)->getJson("/clients/api/profile/orders/{$order->uuid}");
        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $orderProduct->order_uuid,
                'products' => [[
                    'uuid' => $orderProduct->uuid,
                    'rating' => 5,
                    'rating_comment' => 'test'
                ]]
            ],
        ]);
    }

    /**
     *
     */
    public function testCalculate()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create([
            'bonus_balance' => 100
        ]);
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var Product $product1 */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $store->uuid,
        ]);
        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $store->uuid,
        ]);
        /** @var Product $product3 */
        $product3 = factory(Product::class)->create([
            'user_uuid' => $store->uuid,
            'price' => 55
        ]);

        $quantity1 = $this->faker->randomFloat(3, 1, 10);
        $quantity2 = $this->faker->randomFloat(3, 1, 10);
        $quantity3 = 0.111;
        $data = [
            'order' => [
                'store_user_uuid' => $store->uuid,
                'paid_bonus' => 2
            ],
            'products' => [
                [
                    'quantity' => $quantity1,
                    'assortment_uuid' => $product1->assortment_uuid,
                ],
                [
                    'quantity' => $quantity2,
                    'assortment_uuid' => $product2->assortment_uuid,
                ],
                [
                    'quantity' => $quantity3,
                    'assortment_uuid' => $product3->assortment_uuid,
                ]
            ]
        ];

        $json = $this->be($self)->postJson("/clients/api/profile/orders/calculate?admin_debug=", $data);
        $json->assertSuccessful();

        $totalWeight = FloatHelper::round($product1->assortment->weight * $quantity1)
            + FloatHelper::round($product2->assortment->weight * $quantity2)
            + FloatHelper::round($product3->assortment->weight * $quantity3);
        $totalWeight = FloatHelper::round($totalWeight);
        $totalQuantity = FloatHelper::round($quantity1 + $quantity2 + $quantity3);

        $json->assertJson([
            'data' => [
                'uuid' => null,
                'total_weight' => $totalWeight,
                'total_quantity' => $totalQuantity
            ]
        ]);
    }

    /**
     * @testWith [false]
     *           [true]
     *           [true, true]
     *           [true, false, true]
     */
    public function testStore(bool $isOnline = false, bool $testLowPrice = false, bool $useCardWithPaymentSetting = false)
    {
        /** @var Client $self */
        $self = factory(Client::class)->create([
            'bonus_balance' => 100
        ]);
        /** @var Order $order */
        $order = factory(Order::class)->make([
            'order_payment_type_id' => OrderPaymentType::ID_ONLINE
        ]);
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var Product $product1 */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $store->uuid,
            'price' => $this->faker->randomFloat(2, 10, 100)
        ]);
        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $store->uuid,
        ]);

        $quantity1 = $this->faker->randomFloat(3, 1, 10);
        $quantity2 = $this->faker->randomFloat(3, 1, 10);

        $from = CarbonImmutable::now()->addDay()->setHour(10);
        $to = $from->setHour(20);

        $bonuses = $this->faker->numberBetween(1, 10);
        $data = [
            'order' => [
                'store_user_uuid' => $store->uuid,
                'order_delivery_type_id' => $order->order_delivery_type_id,
                'order_payment_type_id' => $isOnline ? OrderPaymentType::ID_ONLINE : OrderPaymentType::ID_CASH,

                'client_email' => $this->faker->email,

                'client_address_data' => [
                    'address' => $this->faker->address,
                    'floor' => $this->faker->numberBetween(1, 10),
                    'entrance' => $this->faker->numberBetween(1, 10),
                    'apartment_number' => $this->faker->numberBetween(1, 10),
                    'intercom_code' => $this->faker->numerify('#####'),
                ],

                'paid_bonus' => $bonuses,

                'planned_delivery_datetime_from' => $from->format('Y-m-d H:i:sO'),
                'planned_delivery_datetime_to' => $to->format('Y-m-d H:i:sO'),
            ],
            'products' => [
                [
                    'quantity' => $quantity1,
                    'assortment_uuid' => $product1->assortment_uuid,
                ],
                [
                    'quantity' => $quantity2,
                    'assortment_uuid' => $product2->assortment_uuid,
                ]
            ]
        ];

        if ($testLowPrice) {
            SystemOrderSetting::query()->where('id', SystemOrderSetting::ID_MIN_PRICE)->update(['value' => '99999999']);
            $json = $this->be($self)->postJson("/clients/api/profile/orders", $data);
            $json->assertStatus(400);
            $json->assertJson([
                'message' => 'You have to add more products to reach minimum price',
                'code' => 2011
            ]);
            return;
        }

        if ($isOnline) {
            $createCardData = [
                'client_uuid' => $self->uuid
            ];
            if ($useCardWithPaymentSetting) {
                /** @var PaymentVendorSetting $setting */
                $setting = PaymentVendorSetting::factory()->createOne();
                $createCardData['payment_vendor_setting_uuid'] = $setting->uuid;
                $store->paymentVendorSettings()->sync([
                    $setting->uuid => ['is_active' => true]
                ]);

                //// DEBUUUUUG
//                $createCardData['binding_id'] = '2e2f23ef-000f-5000-a000-1f2cbfa9516f';
            }

            /** @var ClientCreditCard $card */
            $card = ClientCreditCard::factory()->createOne($createCardData);
            $data['order']['client_credit_card_uuid'] = $card->uuid;
            $acquireOrderId = $this->faker->uuid;

            $acquireResolver = $this->createMock(AcquireResolverInterface::class);
            $this->app->instance(AcquireResolverInterface::class, $acquireResolver);
            $acquire = $this->createMock(AcquireInterface::class);

            $acquireResolver
                ->method('resolveByClientCard')
                ->willReturn($acquire);

            $acquire
                ->expects($this->once())
                ->method('registerAutoPayment')
                ->with(
                    $card->binding_id,
                    $self->uuid,
                    $this->anything(),
                    $this->anything(),
                    $this->anything(),
                    $this->anything(),
                    true
                )
                ->willReturn(new CreatedPaymentDto(
                    $acquireOrderId,
                    $this->faker->url,
                    new PaymentStatusDto(1, PaymentStatusEnum::CREATED, null, null)
                ));

            $acquire
                ->method('isConfirmationNeeded')
                ->willReturn(true);

            $acquire
                ->expects($this->once())
                ->method('paymentOrderBinding')
                ->with($acquireOrderId, $card->binding_id)
                ->willReturn(null);

            $acquire
                ->expects($this->once())
                ->method('getPaymentStatus')
                ->with($acquireOrderId)
                ->willReturn(new PaymentStatusDto(
                    123,
                    PaymentStatusEnum::APPROVED,
                    null,
                    null
                ));

            $atol = $this->mockAtolOnlineClientInterface();
            $atol
                ->expects($this->once())
                ->method('sell');
        }

        $geocoded = new Address(
            'test',
            new AdminLevelCollection(),
            new Coordinates($store->address_latitude + 0.0001, $store->address_longitude + 0.0001),
            null,
            '6Ас2',
            'проезд Добролюбова',
            '',
            'Москва'
        );

        $provider = $this->createMock(ProviderAndDumperAggregator::class);
        $provider
            ->method('get')
            ->willReturn(collect([$geocoded]));
        Geocoder::partialMock()
            ->shouldReceive('geocode')
            ->andReturn($provider);

        $totalWeight = FloatHelper::round($product1->assortment->weight * $quantity1)
            + FloatHelper::round($product2->assortment->weight * $quantity2);
        $totalWeight = FloatHelper::round($totalWeight);

        $totalQuantity = FloatHelper::round($quantity1 + $quantity2);

        $totalAmountWithDiscount1 = MoneyHelper::of($product1->price)->multipliedBy($quantity1);
        $totalAmountWithDiscount1 = MoneyHelper::toFloat($totalAmountWithDiscount1);

        $totalAmountWithDiscount2 = MoneyHelper::of($product2->price)->multipliedBy($quantity2);
        $totalAmountWithDiscount2 = MoneyHelper::toFloat($totalAmountWithDiscount2);

        $total = MoneyHelper::of($totalAmountWithDiscount1)->plus($totalAmountWithDiscount2);
        $total = MoneyHelper::toFloat($total);

        $b1 = MoneyHelper::of($totalAmountWithDiscount1)
            ->dividedBy($total)
            ->multipliedBy($bonuses);
        $b1 = MoneyHelper::toBonus($b1);

        $b2 = MoneyHelper::of($totalAmountWithDiscount2)
            ->dividedBy($total)
            ->multipliedBy($bonuses);
        $b2 = MoneyHelper::toBonus($b2);
        $totalBonuses = $b1 + $b2;
        $diff = $totalBonuses - $bonuses;
        if ($diff != 0) {
            if ($totalAmountWithDiscount1 > $totalAmountWithDiscount2) {
                $b1 += $diff > 0 ? -1 : 1;
            } else {
                $b2 += $diff > 0 ? -1 : 1;
            }
        }

        $json = $this->be($self)->postJson("/clients/api/profile/orders", $data);
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [
                'total_weight' => $totalWeight,
                'total_quantity' => $totalQuantity,
                'client_address_data' => [
                    'address' => 'Москва, проезд Добролюбова, 6Ас2',
                    'latitude' => $store->address_latitude + 0.0001,
                    'longitude' => $store->address_longitude + 0.0001
                ]
            ]
        ]);

        if ($b1 !== 0) {
            $newPrice1WithDiscountAndBonus1 = MoneyHelper::of($totalAmountWithDiscount1 - $b1)
                ->dividedBy($quantity1);
            $newPrice1WithDiscountAndBonus1 = MoneyHelper::toFloat($newPrice1WithDiscountAndBonus1); // Rounding here
            $totalAmountWithDiscount1 = MoneyHelper::of($newPrice1WithDiscountAndBonus1)->multipliedBy($quantity1);
            $totalAmountWithDiscount1 = MoneyHelper::toFloat($totalAmountWithDiscount1);
        }
        if ($b2 !== 0) {
            $newPrice1WithDiscountAndBonus2 = MoneyHelper::of($totalAmountWithDiscount2 - $b2)
                ->dividedBy($quantity2);
            $newPrice1WithDiscountAndBonus2 =  MoneyHelper::toFloat($newPrice1WithDiscountAndBonus2); // Rounding here
            $totalAmountWithDiscount2 = MoneyHelper::of($newPrice1WithDiscountAndBonus2)->multipliedBy($quantity2);
            $totalAmountWithDiscount2 = MoneyHelper::toFloat($totalAmountWithDiscount2);
        }

        if ($isOnline) {
            $this->assertDatabaseHas('client_payments', [
                'generated_order_uuid' => $acquireOrderId,
                'order_status' => PaymentStatusEnum::APPROVED,
                'binding_id' => $card->binding_id,
                'client_uuid' => $self->uuid,
                'related_reference_type' => 'order',
            ]);
        }

        $orderId = $json->json('data.uuid');
        $this->assertDatabaseHas('orders', [
            'client_uuid' => $self->uuid,
            'store_user_uuid' => $store->uuid,
            'client_credit_card_uuid' => $isOnline ? $card->uuid : null,
        ]);
        $this->assertDatabaseHas('order_products', [
            'product_uuid' => $product1->uuid,
            'total_amount_with_discount' => $totalAmountWithDiscount1
        ]);
        $this->assertDatabaseHas('order_products', [
            'product_uuid' => $product2->uuid,
            'total_amount_with_discount' => $totalAmountWithDiscount2
        ]);
        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product1->uuid,
            'quantity_old' => $product1->quantity,
            'quantity_new' => $product1->quantity - $quantity1
        ]);
        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product2->uuid,
            'quantity_old' => $product2->quantity,
            'quantity_new' => $product2->quantity - $quantity2,
            'reference_type' => Order::MORPH_TYPE_ALIAS,
            'reference_id' => $orderId
        ]);
        $this->assertDatabaseHas('client_bonus_transactions', [
            'client_uuid' => $self->uuid,
            'quantity_old' => 100,
            'quantity_delta' => -$bonuses,
            'quantity_new' => 100 - $bonuses,
            'related_reference_type' => Order::MORPH_TYPE_ALIAS,
            'related_reference_id' => $orderId,
            'reason' => ClientBonusTransaction::REASON_PURCHASE_PAID,
        ]);

        $self->refresh();
        $this->assertEquals(100 - $bonuses, $self->bonus_balance);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function testStoreCantMakeForToday(bool $delivery = false)
    {
        Config::set('app.order.meta.cant_make_for_today_after', '00:00');
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var Order $order */
        $order = factory(Order::class)->make([
            'order_payment_type_id' => OrderPaymentType::ID_ONLINE,
            'order_delivery_type_id' => $delivery ? OrderDeliveryType::ID_DELIVERY : OrderDeliveryType::ID_PICKUP,
        ]);
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var Product $product1 */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $store->uuid,
            'price' => $this->faker->randomFloat(2, 10, 100)
        ]);

        $quantity1 = $this->faker->randomFloat(3, 1, 10);

        $from = CarbonImmutable::now()->addMinute(1);
        $to = $from->addHour(1);

        if ($delivery) {
            $geocoded = new Address(
                'test',
                new AdminLevelCollection(),
                new Coordinates($store->address_latitude + 0.0001, $store->address_longitude + 0.0001),
                null,
                '6Ас2',
                'проезд Добролюбова',
                '',
                'Москва'
            );

            $provider = $this->createMock(ProviderAndDumperAggregator::class);
            $provider
                ->method('get')
                ->willReturn(collect([$geocoded]));
            Geocoder::partialMock()
                ->shouldReceive('geocode')
                ->andReturn($provider);
        }

        $data = [
            'order' => [
                'store_user_uuid' => $store->uuid,
                'order_delivery_type_id' => $order->order_delivery_type_id,
                'order_payment_type_id' => OrderPaymentType::ID_CASH,

                'client_email' => $this->faker->email,

                'client_address_data' => [
                    'address' => $this->faker->address,
                    'floor' => $this->faker->numberBetween(1, 10),
                    'entrance' => $this->faker->numberBetween(1, 10),
                    'apartment_number' => $this->faker->numberBetween(1, 10),
                    'intercom_code' => $this->faker->numerify('#####'),
                ],

                'planned_delivery_datetime_from' => $from->format('Y-m-d H:i:sO'),
                'planned_delivery_datetime_to' => $to->format('Y-m-d H:i:sO'),
            ],
            'products' => [
                [
                    'quantity' => $quantity1,
                    'assortment_uuid' => $product1->assortment_uuid,
                ]
            ]
        ];

        $json = $this->be($self)->postJson("/clients/api/profile/orders", $data);
        $json->assertStatus(400);
        $json->assertJson([
            'message' => 'Can\'t make order for today',
            'code' => 2012
        ]);
        return;
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
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_payment_type_id' => OrderPaymentType::ID_CASH,
            'client_uuid' => $self->uuid,
            'order_status_id' => $current
        ]);

        $data = [
            'order_status_id' => $new
        ];

        $json = $this->be($self)->postJson("/clients/api/profile/orders/$order->uuid/set-status", $data);
        if ($shouldBeAnError) {
            $json->assertStatus(Response::HTTP_BAD_REQUEST);
        } else {
            $json->assertSuccessful();
        }

        $order->refresh();
        $this->assertEquals($order->order_status_id, $shouldBeAnError ? $current : $new);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function testSetProductRating(bool $restricted = false)
    {
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_status_id' => OrderStatus::ID_DONE
        ]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid
        ]);

        /** @var \App\Models\PromoDiverseFoodClientStat $stat */
        $stat = PromoDiverseFoodClientStat::factory()->createOne([
            'client_uuid' => $order->client_uuid,
            'month' => now()->format('Y-m'),
            'purchased_count' => 1,
            'rated_count' => 0
        ]);
        PromoDiverseFoodClientStatAssortment::factory()->createOne([
            'promo_diverse_food_client_stat_uuid' => $stat->uuid,
            'assortment_uuid' => $orderProduct->product->assortment_uuid,
            'is_rated' => false
        ]);

        $self = $order->client;

        $ratingScoreMoment = $restricted ? now() : now()->subMonth();
        factory(RatingScore::class)->create([
            'rated_reference_type' => Assortment::MORPH_TYPE_ALIAS,
            'rated_reference_id' => $orderProduct->product->assortment_uuid,
            'rated_by_reference_type' => Client::MORPH_TYPE_ALIAS,
            'rated_by_reference_id' => $orderProduct->order->client_uuid,
            'rated_through_reference_type' => OrderProduct::MORPH_TYPE_ALIAS,
            'rated_through_reference_id' => $orderProduct->uuid,
            'created_at' => $ratingScoreMoment,
            'updated_at' => $ratingScoreMoment,
        ]);

        $json = $this->be($self)->postJson(
            "/clients/api/profile/orders/products/{$orderProduct->uuid}/set-rating",
            [
                'value' => 4,
                'comment' => 'hello kitty',
            ]
        );

//        if ($restricted) {
//            $json->assertStatus(400);
//            return;
//        }

        $json->assertSuccessful();
        $this->assertDatabaseHas('rating_scores', [
            'rated_reference_type' => Assortment::MORPH_TYPE_ALIAS,
            'rated_reference_id' => $orderProduct->product->assortment_uuid,
            'rated_by_reference_type' => Client::MORPH_TYPE_ALIAS,
            'rated_by_reference_id' => $orderProduct->order->client_uuid,
            'rated_through_reference_type' => OrderProduct::MORPH_TYPE_ALIAS,
            'rated_through_reference_id' => $orderProduct->uuid,
            'value' => 4,
            'additional_attributes->comment' => 'hello kitty',
            'additional_attributes->weight' => 1,
        ]);

        $this->assertDatabaseHas('ratings', [
            'reference_type' => Assortment::MORPH_TYPE_ALIAS,
            'reference_id' => $orderProduct->product->assortment_uuid,
            'rating_type_id' => RatingType::ID_COMMON,
            'value' => 4.0,
        ]);

        $this->assertDatabaseHas('promo_diverse_food_client_stats', [
            'client_uuid' => $self->uuid,
            'purchased_count' => 1,
            'rated_count' => 1,
        ]);
    }

    /**
     * @return array[]
     */
    public function setStatusDataProvider(): array
    {
        return [
            [OrderStatus::ID_NEW, OrderStatus::ID_CANCELLED],
            [OrderStatus::ID_COLLECTING, OrderStatus::ID_COLLECTED, true],
            [OrderStatus::ID_DONE, OrderStatus::ID_CANCELLED, true],
        ];
    }
}
