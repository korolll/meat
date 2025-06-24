<?php

namespace Tests\Feature\API;

use App\Jobs\ProcessOrderPaymentJob;
use App\Models\Assortment;
use App\Models\AssortmentUnit;
use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\Order;
use App\Models\OrderDeliveryType;
use App\Models\OrderPaymentType;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\PromoFavoriteAssortmentSetting;
use App\Models\User;
use App\Services\Management\Client\Bonus\MaxBonusesCalculator;
use App\Services\Management\Client\Order\OrderDeliveryPriceCalculatorInterface;
use App\Services\Management\Client\Order\OrderFinalPriceResolver;
use App\Services\Management\Client\Order\OrderPriceResolver;
use App\Services\Management\Client\Order\Payment\PaymentStatusEnum;
use App\Services\Management\Client\Product\CalculateContextInterface;
use App\Services\Management\Client\Product\ClientProductCollectionPriceCalculator;
use App\Services\Management\Client\Product\ClientProductPaidBonusApplier;
use App\Services\Management\Client\Product\ClientProductPriceCalculator;
use App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverPreloadInterface;
use App\Services\Management\Client\Product\Discount\DiscountData;
use App\Services\Management\Client\Product\Discount\DiscountDataInterface;
use App\Services\Management\Client\Product\SimpleClientBulkProductPriceCalculator;
use App\Services\Money\Acquire\AcquireInterface;
use App\Services\Money\Acquire\Data\CreatedPaymentDto;
use App\Services\Money\Acquire\Data\PaymentStatusDto;
use App\Services\Money\Acquire\Resolver\AcquireResolverInterface;
use App\Services\Money\MoneyHelper;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Tests\TestCaseNotificationsFake;

class OrderTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex()
    {
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create();
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/orders');

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
        /** @var Order $order */
        $order = factory(Order::class)->create();

        /** @var ClientPayment $payment */
        $payment = ClientPayment::factory()->makeOne([
            'client_uuid' => $order->client_uuid,
            'related_reference_type' => null,
            'related_reference_id' => null
        ]);
        $payment->relatedReference()->associate($order);
        $payment->save();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/orders/{$order->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $order->uuid,
                'payments' => [[
                    'uuid' => $payment->uuid
                ]]
            ],
        ]);
    }

    /**
     *
     */
    public function testUpdate()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create();

        $data = [
            'client_comment' => $this->faker->word
        ];

        $json = $this->be($self)->putJson("/api/orders/$order->uuid", $data);
        $json->assertSuccessful();
        $this->assertDatabaseHas('orders', [
            'uuid' => $order->uuid,
            'client_comment' => $data['client_comment']
        ]);
    }

    /**
     * @testWith [null]
     *           [true]
     *           [false]
     */
    public function testRetryPayment($deposit = null)
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_payment_type_id' => OrderPaymentType::ID_ONLINE
        ]);

        $data = [
            'client_comment' => $this->faker->word
        ];

        $depositStr = '';
        if ($deposit !== null) {
            $depositStr = $deposit ? '1' : '0';
        }

        Bus::fake();

        $json = $this->be($self)->getJson("/api/orders/$order->uuid/retry-payment/$depositStr", $data);
        $json->assertSuccessful();

        $expect = $deposit === null || (bool)$deposit;
        // Assert that a job was dispatched...
        Bus::assertDispatched(ProcessOrderPaymentJob::class, function (ProcessOrderPaymentJob $job) use ($expect, $order) {
            $this->assertEquals($order->uuid, $job->order->uuid);

            return $job;
        });
    }

    /**
     * @param string $current
     * @param string $new
     * @param false  $shouldBeAnError
     * @param array  $orderAttrs
     *
     * @dataProvider setStatusDataProvider
     */
    public function testSetStatus(string $current, string $new, bool $shouldBeAnError = false, array $orderAttrs = [])
    {
        if (! isset($orderAttrs['order_payment_type_id'])) {
            $orderAttrs['order_payment_type_id'] = OrderPaymentType::ID_CASH;
        }
        if (! isset($orderAttrs['order_delivery_type_id'])) {
            $orderAttrs['order_delivery_type_id'] =  OrderDeliveryType::ID_DELIVERY;
        }
        if (! isset($orderAttrs['total_price_for_products_with_discount'])) {
            $orderAttrs['total_price_for_products_with_discount'] = 100;
        }
        $orderAttrs['order_status_id'] = $current;

        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create($orderAttrs);
        /** @var OrderProduct $product */
        $product = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid,
        ]);

        PromoFavoriteAssortmentSetting::factory()->createOne([
            'threshold_amount' => 1,
            'number_of_sum_days' => 10
        ]);

        $data = [
            'order_status_id' => $new
        ];

        $json = $this->be($self)->postJson("/api/orders/$order->uuid/set-status", $data);
        if ($shouldBeAnError) {
            $json->assertStatus(Response::HTTP_BAD_REQUEST);
        } else {
            $json->assertSuccessful();
        }

        $order->refresh();
        $this->assertEquals($order->order_status_id, $shouldBeAnError ? $current : $new);

        if (! $shouldBeAnError && $new === OrderStatus::ID_DONE) {
            $this->assertDatabaseHas('promo_diverse_food_client_stats', [
                'client_uuid' => $order->client_uuid,
                'purchased_count' => 1,
                'rated_count' => 0,
            ]);

            $this->assertDatabaseHas('client_promo_favorite_assortment_variants', [
                'client_uuid' => $order->client_uuid,
            ]);

            $this->assertDatabaseHas('promotion_in_the_shop_last_purchases', [
                'client_uuid' => $order->client_uuid,
                'catalog_uuid' => $product->product->assortment->catalog_uuid,
            ]);
        }
    }

    /**
     *
     */
    public function testSetCancelStatus()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_status_id' => OrderStatus::ID_NEW
        ]);
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid
        ]);
        $product = $orderProduct->product;

        $data = [
            'order_status_id' => OrderStatus::ID_CANCELLED
        ];

        $json = $this->be($self)->postJson("/api/orders/$order->uuid/set-status", $data);
        $json->assertSuccessful();

        $order->refresh();
        $this->assertEquals($order->order_status_id, OrderStatus::ID_CANCELLED);
        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $product->quantity,
            'quantity_delta' => $orderProduct->quantity
        ]);
    }

    /**
     *
     */
    public function testSetNewFromCancelStatus()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_status_id' => OrderStatus::ID_CANCELLED
        ]);
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid
        ]);
        $product = $orderProduct->product;

        $data = [
            'order_status_id' => OrderStatus::ID_NEW
        ];

        Config::set('app.order.status-transitions.' . User::class . '.admin.' . OrderStatus::ID_CANCELLED . '.' . OrderStatus::ID_NEW, 1);
        $json = $this->be($self)->postJson("/api/orders/$order->uuid/set-status", $data);
        $json->assertSuccessful();

        $order->refresh();
        $this->assertEquals($order->order_status_id, OrderStatus::ID_NEW);
        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $product->quantity,
            'quantity_delta' => -$orderProduct->quantity
        ]);
    }

    /**
     * @return void
     */
    public function testSetCollectedFromCollectingStatusForOnlinePriceMinus()
    {
        Config::set('app.order.payment.enable_new_data', 0);
        Config::set('app.order.payment.enable_new_data_for_clients', []);

        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_status_id' => OrderStatus::ID_COLLECTING,
            'order_payment_type_id' => OrderPaymentType::ID_ONLINE,
            'total_price' => 8,
            'is_paid' => true
        ]);
        $order->refresh();

        /** @var ClientPayment $payment */
        $payment = ClientPayment::factory()->createOne([
            'client_uuid' => $order->client_uuid,
            'related_reference_type' => Order::MORPH_TYPE_ALIAS,
            'related_reference_id' => $order->uuid,
            'order_status' => PaymentStatusEnum::DEPOSITED,
            'amount' => 1000
        ]);

        $data = [
            'order_status_id' => OrderStatus::ID_COLLECTED
        ];

        $acquireResolver = $this->createMock(AcquireResolverInterface::class);
        $this->app->instance(AcquireResolverInterface::class, $acquireResolver);
        $acquire = $this->createMock(AcquireInterface::class);

        $acquireResolver
            ->method('resolveByClientCard')
            ->willReturn($acquire);
        $acquire
            ->expects($this->once())
            ->method('refund')
            ->with($payment->generated_order_uuid, 200);

        $json = $this->be($self)->postJson("/api/orders/$order->uuid/set-status", $data);
        $json->assertSuccessful();

        $order->refresh();
        $this->assertEquals($order->order_status_id, OrderStatus::ID_COLLECTED);
        $this->assertTrue($order->is_paid);

        $payment->refresh();
        $this->assertEquals(200, $payment->refunded_amount);
    }

    /**
     * @return void
     */
    public function testSetCollectedFromCollectingStatusForOnlineLowerPaid()
    {
        Config::set('app.order.payment.enable_new_data', 0);
        Config::set('app.order.payment.enable_new_data_for_clients', []);

        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_status_id' => OrderStatus::ID_COLLECTING,
            'order_payment_type_id' => OrderPaymentType::ID_ONLINE,
            'total_price' => 10,
            'is_paid' => true
        ]);
        $order->refresh();

        /** @var ClientPayment $payment */
        $payment = ClientPayment::factory()->createOne([
            'client_uuid' => $order->client_uuid,
            'related_reference_type' => Order::MORPH_TYPE_ALIAS,
            'related_reference_id' => $order->uuid,
            'order_status' => PaymentStatusEnum::DEPOSITED,
            'amount' => 800
        ]);

        $data = [
            'order_status_id' => OrderStatus::ID_COLLECTED
        ];

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
                $this->anything(),
                $order->client_uuid,
                $order->number . '-1',
                200,
                $this->anything(),
                $this->anything(),
                false
            )
            ->willReturn(new CreatedPaymentDto(
                id: $acquireOrderId,
                confirmationUrl: '',
                statusDto: new PaymentStatusDto(
                    originalStatus: 'created',
                    status: PaymentStatusEnum::CREATED,
                    bindingId: null,
                    cardNumberMasked: null
                )
            ));

        $acquire
            ->method('isConfirmationNeeded')
            ->willReturn(true);

        $acquire
            ->expects($this->once())
            ->method('paymentOrderBinding')
            ->with($acquireOrderId, $order->clientCreditCard->binding_id)
            ->willReturn(null);

        $acquire
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($acquireOrderId)
            ->willReturn(new PaymentStatusDto(
                originalStatus: 'success',
                status: PaymentStatusEnum::DEPOSITED,
                bindingId: null,
                cardNumberMasked: null
            ));

        $json = $this->be($self)->postJson("/api/orders/$order->uuid/set-status", $data);
        $json->assertSuccessful();

        $order->refresh();
        $this->assertEquals($order->order_status_id, OrderStatus::ID_COLLECTED);
        $this->assertTrue($order->is_paid);

        $this->assertDatabaseHas('client_payments', [
            'order_status' => PaymentStatusEnum::DEPOSITED,
            'amount' => 800,
            'client_uuid' => $order->client_uuid,
            'related_reference_type' => 'order',
            'related_reference_id' => $order->uuid
        ]);
        $this->assertDatabaseHas('client_payments', [
            'generated_order_uuid' => $acquireOrderId,
            'order_status' => PaymentStatusEnum::DEPOSITED,
            'binding_id' => $order->clientCreditCard->binding_id,
            'amount' => 200,
            'client_uuid' => $order->client_uuid,
            'related_reference_type' => 'order',
            'related_reference_id' => $order->uuid
        ]);
    }

    /**
     * @return void
     */
    public function testSetCollectedFromCollectingStatusForOnlineWithNewPaymentData()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_status_id' => OrderStatus::ID_COLLECTING,
            'order_payment_type_id' => OrderPaymentType::ID_ONLINE,
            'delivery_price' => 100,
            'is_paid' => true,
            'total_price' => 200
        ]);
        $order->refresh();

        /** @var ClientPayment $payment */
        ClientPayment::factory()->createOne([
            'client_uuid' => $order->client_uuid,
            'related_reference_type' => Order::MORPH_TYPE_ALIAS,
            'related_reference_id' => $order->uuid,
            'order_status' => PaymentStatusEnum::DECLINED
        ]);

        /** @var Assortment $assortment1 */
        $assortment1 = factory(Assortment::class)->create([
            'assortment_unit_id' => AssortmentUnit::ID_PACKAGE
        ]);
        /** @var Assortment $assortment2 */
        $assortment2 = factory(Assortment::class)->create([
            'assortment_unit_id' => AssortmentUnit::ID_KILOGRAM
        ]);

        /** @var Product $product1 */
        $product1 = factory(Product::class)->create([
            'assortment_uuid' => $assortment1->uuid
        ]);
        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'assortment_uuid' => $assortment2->uuid
        ]);

        /** @var OrderProduct $orderProduct1 */
        $orderProduct1 = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid,
            'product_uuid' => $product1->uuid,
            'quantity' => $this->faker->numberBetween(2, 10)
        ]);
        /** @var OrderProduct $orderProduct2 */
        $orderProduct2 = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid,
            'product_uuid' => $product2->uuid,
            'quantity' => $this->faker->randomFloat(3, 2, 10)
        ]);

        $data = [
            'order_status_id' => OrderStatus::ID_COLLECTED
        ];

        Config::set('app.order.payment.enable_new_data', 0);
        Config::set('app.order.payment.enable_new_data_for_clients', [
            $order->client_uuid => 0
        ]);

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
                $this->anything(),
                $order->client_uuid,
                $order->number . '-1',
                $order->total_price_kopek,
                $this->anything(),
                $this->anything(),
                false
            )
            ->willReturn(new CreatedPaymentDto(
                id: $acquireOrderId,
                confirmationUrl: '',
                statusDto: new PaymentStatusDto(
                    status: PaymentStatusEnum::CREATED,
                    originalStatus: PaymentStatusEnum::CREATED->value,
                    bindingId: null,
                    cardNumberMasked: null
                )
            ));

        $acquire
            ->method('isConfirmationNeeded')
            ->willReturn(true);

        $acquire
            ->expects($this->once())
            ->method('paymentOrderBinding')
            ->with($acquireOrderId, $order->clientCreditCard->binding_id)
            ->willReturn(null);

        $acquire
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($acquireOrderId)
            ->willReturn(new PaymentStatusDto(
                status: PaymentStatusEnum::DEPOSITED,
                originalStatus: PaymentStatusEnum::DEPOSITED->value,
                bindingId: null,
                cardNumberMasked: null
            ));

        $json = $this->be($self)->postJson("/api/orders/$order->uuid/set-status", $data);
        $json->assertSuccessful();

        $order->refresh();
        $this->assertEquals($order->order_status_id, OrderStatus::ID_COLLECTED);
        $this->assertTrue($order->is_paid);

        $this->assertDatabaseHas('client_payments', [
            'generated_order_uuid' => $acquireOrderId,
            'order_status' => PaymentStatusEnum::DEPOSITED,
            'binding_id' => $order->clientCreditCard->binding_id,
            'amount' => $order->total_price_kopek,
            'client_uuid' => $order->client_uuid,
            'related_reference_type' => 'order',
            'related_reference_id' => $order->uuid
        ]);
    }

    /**
     *
     */
    public function testSetCancelledFromCollectedStatusForOnline()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'order_status_id' => OrderStatus::ID_COLLECTING,
            'order_payment_type_id' => OrderPaymentType::ID_ONLINE,
            'is_paid' => true,
            'total_price' => 10
        ]);

        /** @var ClientPayment $payment */
        $payment = ClientPayment::factory()->createOne([
            'client_uuid' => $order->client_uuid,
            'related_reference_type' => Order::MORPH_TYPE_ALIAS,
            'related_reference_id' => $order->uuid,
            'order_status' => PaymentStatusEnum::DEPOSITED,
            'amount' => $order->total_price_kopek
        ]);

        $data = [
            'order_status_id' => OrderStatus::ID_CANCELLED
        ];

        $acquireResolver = $this->createMock(AcquireResolverInterface::class);
        $this->app->instance(AcquireResolverInterface::class, $acquireResolver);
        $acquire = $this->createMock(AcquireInterface::class);

        $acquireResolver
            ->method('resolveByClientCard')
            ->willReturn($acquire);
        $acquire
            ->expects($this->once())
            ->method('refund')
            ->with($payment->generated_order_uuid, $order->total_price_kopek);

        $atol = $this->mockAtolOnlineClientInterface();
        $atol
            ->expects($this->once())
            ->method('sellRefund');

        $json = $this->be($self)->postJson("/api/orders/$order->uuid/set-status", $data);
        $json->assertSuccessful();

        $order->refresh();
        $payment->refresh();

        $this->assertEquals($order->order_status_id, OrderStatus::ID_CANCELLED);
        $this->assertFalse($order->is_paid);
        $this->assertEquals($payment->order_status, PaymentStatusEnum::REFUNDED);
    }

    /**
     * @return array[]
     */
    public function setStatusDataProvider(): array
    {
        return [
            [OrderStatus::ID_NEW, OrderStatus::ID_COLLECTING],
            [OrderStatus::ID_NEW, OrderStatus::ID_CANCELLED],
            [OrderStatus::ID_NEW, OrderStatus::ID_DONE, true],

            [OrderStatus::ID_COLLECTING, OrderStatus::ID_COLLECTED],
            [OrderStatus::ID_COLLECTING, OrderStatus::ID_CANCELLED],
            [OrderStatus::ID_COLLECTING, OrderStatus::ID_DONE, true],

            [OrderStatus::ID_DELIVERING, OrderStatus::ID_DONE],
            [OrderStatus::ID_DELIVERING, OrderStatus::ID_CANCELLED],
            [OrderStatus::ID_DELIVERING, OrderStatus::ID_COLLECTING, true],

            [OrderStatus::ID_DONE, OrderStatus::ID_CANCELLED],
            [OrderStatus::ID_DONE, OrderStatus::ID_NEW, true],

            [OrderStatus::ID_COLLECTED, OrderStatus::ID_DELIVERING, true, ['is_paid' => false, 'order_payment_type_id' => OrderPaymentType::ID_ONLINE]],
            [OrderStatus::ID_COLLECTED, OrderStatus::ID_DONE, false, ['is_paid' => false, 'order_payment_type_id' => OrderPaymentType::ID_ONLINE]],
            [OrderStatus::ID_DELIVERING, OrderStatus::ID_DONE, true, ['is_paid' => false, 'order_payment_type_id' => OrderPaymentType::ID_ONLINE]],
        ];
    }

    /**
     * @throws \Brick\Money\Exception\MoneyMismatchException
     *
     * @testWith [false]
     *           [true]
     */
    public function testOrderPriceResolver(bool $useBonuses)
    {
        /** @var Assortment $assortment1 */
        $assortment1 = factory(Assortment::class)->create([
            'bonus_percent' => 50
        ]);
        /** @var Product $pr1 */
        $pr1 = factory(Product::class)->create([
            'assortment_uuid' => $assortment1->uuid
        ]);
        /** @var Product $pr2 */
        $pr2 = factory(Product::class)->create();
        /** @var $client $client */
        $client = factory(Client::class)->create();

        $minusValue = 1.15;
        $discResolver = new class($minusValue) implements ClientProductDiscountResolverPreloadInterface {
            public function __construct($minusValue)
            {
                $this->minusValue = $minusValue;
            }

            public function resolve(CalculateContextInterface $ctx, Product $product): ?DiscountDataInterface
            {
                return DiscountData::create($product->price - $this->minusValue, $product);
            }

            public function preLoad(CalculateContextInterface $ctx, iterable $products): void
            {
            }

            public function clearPreloadedData(): void
            {
            }
        };

        $priceBulkCalculator = new SimpleClientBulkProductPriceCalculator(new ClientProductPriceCalculator($discResolver));
        $calc = new ClientProductCollectionPriceCalculator($priceBulkCalculator);
        $bonusApplier = new ClientProductPaidBonusApplier();

        $deliveryPrice = $this->faker->randomFloat(2, 10, 100);
        $quantities = 10;

        $order = new Order();
        $collection = new Collection();
        $orderProduct1 = new OrderProduct();
        $orderProduct1->setRelation('product', $pr1);
        $orderProduct1->quantity = $quantities;
        $orderProduct2 = new OrderProduct();
        $orderProduct2->setRelation('product', $pr2);
        $orderProduct2->quantity = $quantities;

        $collection->add($orderProduct1);
        $collection->add($orderProduct2);

        $order->setRelation('orderProducts', $collection);
        $order->setRelation('client', $client);

        $discResolver = new class($deliveryPrice) implements OrderDeliveryPriceCalculatorInterface {
            public function __construct($deliveryPrice)
            {
                $this->deliveryPrice = $deliveryPrice;
            }

            public function calculate(Order $order): float
            {
                return $this->deliveryPrice;
            }
        };

        $maxBonusesPercent = 30;

        $bonusesToGet = MoneyHelper::of($pr1->price)
            ->minus($minusValue)
            ->dividedBy(2)
            ->multipliedBy($quantities);
        $bonusesToGet = MoneyHelper::toBonus($bonusesToGet);

        $expectedTotalRaw = ($pr1->price + $pr2->price) * $quantities - $minusValue * $quantities * 2;
        $expectedTotal = round($expectedTotalRaw + $deliveryPrice, 2);

        $maxBonuses = MoneyHelper::of($expectedTotal)
            ->multipliedBy($maxBonusesPercent)
            ->dividedBy(100);
        $maxBonuses = MoneyHelper::toBonus($maxBonuses);
        if ($useBonuses) {
            $expectedTotalRaw -= $maxBonuses;
            $expectedFinalTotal = $expectedTotal - $maxBonuses;
            $provideBonus = $maxBonuses;
        } else {
            $expectedFinalTotal = $expectedTotal;
            $provideBonus = 0;
        }

        $maxResolver = new MaxBonusesCalculator($maxBonusesPercent);
        $finalPriceResolver = new OrderFinalPriceResolver($discResolver, $maxResolver);
        $resolver = new OrderPriceResolver($calc, $finalPriceResolver, $bonusApplier);
        $resolver->resolve($order, $provideBonus);

        $this->assertEquals($quantities * 2, $order->total_quantity, 'Failing asserting total_quantity');
        $this->assertEquals(MoneyHelper::round($expectedTotalRaw), $order->total_price_for_products_with_discount, 'Failing asserting total_price_for_products_with_discount');
        $this->assertEquals(MoneyHelper::round($expectedFinalTotal), $order->total_price, 'Failing asserting total_price');
        $this->assertEquals(MoneyHelper::round($pr1->assortment->weight + $pr2->assortment->weight) * 10, $order->total_weight, 'Failing asserting total_weight');
        $this->assertEquals($bonusesToGet, $order->total_bonus, 'Failing asserting total_bonus');

        $this->assertEquals($pr1->uuid, $orderProduct1->discountable_uuid);
        $this->assertEquals($pr2->uuid, $orderProduct2->discountable_uuid);
    }
}
