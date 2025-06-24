<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Client;
use App\Models\ClientCreditCard;
use App\Models\ClientPayment;
use App\Models\Order;
use App\Models\OrderPaymentType;
use App\Models\OrderStatus;
use App\Models\PaymentVendor;
use App\Models\PaymentVendorSetting;
use App\Services\Management\Client\Order\Payment\PaymentStatusEnum;
use App\Services\Money\Acquire\AcquireInterface;
use App\Services\Money\Acquire\Data\CreatedPaymentDto;
use App\Services\Money\Acquire\Data\PaymentStatusDto;
use App\Services\Money\Acquire\Resolver\AcquireResolverInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class CreditCardTest extends TestCaseNotificationsFake
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

        /** @var ClientCreditCard $card */
        $card = ClientCreditCard::factory()->createOne([
            'client_uuid' => $self->uuid,
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/credit-cards');
        $json->assertSuccessful()->assertJson([
            'data' => [[
                'uuid' => $card->uuid,
                'card_mask' => $card->card_mask
            ]]
        ]);
    }

    /**
     *
     */
    public function testShow()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var ClientCreditCard $card */
        $card = ClientCreditCard::factory()->createOne([
            'client_uuid' => $self->uuid,
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/credit-cards/' . $card->uuid);
        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $card->uuid,
                'card_mask' => $card->card_mask
            ]
        ]);
    }

    /**
     *
     */
    public function testDestroy()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var ClientCreditCard $card */
        $card = ClientCreditCard::factory()->createOne([
            'client_uuid' => $self->uuid,
        ]);

        $json = $this->be($self)->deleteJson('/clients/api/profile/credit-cards/' . $card->uuid);
        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $card->uuid,
                'card_mask' => $card->card_mask
            ]
        ]);

        $card->refresh();
        $this->assertNotNull($card->deleted_at);
    }

    /**
     *
     */
    public function testDestroyActiveOrder()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var ClientCreditCard $card */
        $card = ClientCreditCard::factory()->createOne([
            'client_uuid' => $self->uuid,
        ]);

        factory(Order::class)->create([
            'client_credit_card_uuid' => $card->uuid,
            'client_uuid' => $self->uuid,
            'order_payment_type_id' => OrderPaymentType::ID_ONLINE,
            'order_status_id' => $this->faker->randomElement([
                OrderStatus::ID_NEW,
                OrderStatus::ID_COLLECTING,
            ])
        ]);

        $json = $this->be($self)->deleteJson('/clients/api/profile/credit-cards/' . $card->uuid);
        $json->assertStatus(400);

        $card->refresh();
        $this->assertNull($card->deleted_at);
    }


    /**
     * @testWith [true]
     *           [false]
     */
    public function testLinkCard(bool $provideStore)
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        $formUrl = $this->faker->url;
        $orderId = $this->faker->uuid;

        $acquireResolver = $this->createMock(AcquireResolverInterface::class);
        $this->app->instance(AcquireResolverInterface::class, $acquireResolver);
        $acquire = $this->createMock(AcquireInterface::class);

        if ($provideStore) {
            /** @var \App\Models\User $user */
            $user = factory(\App\Models\User::class)->state('store')->create();
            $data = [
                'store_uuid' => $user->uuid
            ];

            /** @var PaymentVendorSetting $vendorSetting */
            $vendorSetting = PaymentVendorSetting::factory()->createOne([
                'payment_vendor_id' => PaymentVendor::ID_SBERBANK
            ]);
            $user->paymentVendorSettings()->sync([
                $vendorSetting->uuid => ['is_active' => true]
            ]);

            $acquireResolver
                ->expects($this->once())
                ->method('resolveBySetting')
                ->willReturnCallback(function (PaymentVendorSetting $actualVendorSetting) use ($acquire, $vendorSetting) {
                    $this->assertEquals($vendorSetting->uuid, $actualVendorSetting->uuid);
                    return $acquire;
                });

            $acquire
                ->expects($this->once())
                ->method('getVendorId')
                ->willReturn(PaymentVendor::ID_SBERBANK);
        } else {
            $data = [];
            $acquireResolver
                ->expects($this->once())
                ->method('resolveDefaultByVendor')
                ->with()
                ->willReturn($acquire);
        }

        $dto = new CreatedPaymentDto($orderId, $formUrl, $this->createMock(PaymentStatusDto::class));
        $acquire
            ->expects($this->once())
            ->method('registerPaymentForBinding')
            ->with(
                $self->uuid,
                $this->anything(),
                config('services.sberbank.acquire.bind_card_amount'),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn($dto);

        $json = $this->be($self)->getJson('/clients/api/profile/credit-cards/link?' . http_build_query($data));
        $json->assertSuccessful()->assertJson([
            'data' => [
                'form_url' => $formUrl,
                'order_id' => $orderId
            ]
        ]);

        $this->assertDatabaseHas('client_credit_cards', [
            'client_uuid' => $self->uuid,
            'generated_order_uuid' => $orderId
        ]);

        /** @var ClientCreditCard $card */
        $card = $self->clientCreditCards()->first();
        $this->assertDatabaseHas('client_payments', [
            'client_uuid' => $self->uuid,
            'generated_order_uuid' => $orderId,
            'related_reference_type' => ClientCreditCard::MORPH_TYPE_ALIAS,
            'related_reference_id' => $card->uuid
        ]);

    }

    /**
     *
     */
    public function testLinkCardSuccess()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientCreditCard $card */
        $card = ClientCreditCard::factory()->createOne([
            'client_uuid' => $self->uuid,
            'card_mask' => null,
            'binding_id' => null,
        ]);

//        /** @var ClientCreditCard $card */
//        $card = ClientCreditCard::first();
//        $self = $card->client;

        /** @var ClientPayment $payment */
        $payment = ClientPayment::factory()->createOne([
            'client_uuid' => $self->uuid,
            'generated_order_uuid' => $card->generated_order_uuid,
            'related_reference_type' => ClientCreditCard::MORPH_TYPE_ALIAS,
            'related_reference_id' => $card->uuid
        ]);

        /** @var ClientCreditCard $cardFake */
        $cardFake = ClientCreditCard::factory()->make();
        $acquireResolver = $this->createMock(AcquireResolverInterface::class);
        $this->app->instance(AcquireResolverInterface::class, $acquireResolver);
        $acquire = $this->createMock(AcquireInterface::class);

        $acquireResolver
            ->method('resolveByClientCard')
            ->willReturn($acquire);

        $acquire
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($card->generated_order_uuid)
            ->willReturn(new PaymentStatusDto(
                1,
                PaymentStatusEnum::APPROVED,
                $cardFake->binding_id,
                $cardFake->card_mask
            ));
        $acquire
            ->method('getVendorId')
            ->willReturn(PaymentVendor::ID_SBERBANK);

        $acquire
            ->expects($this->once())
            ->method('refund')
            ->with($card->generated_order_uuid, config('services.sberbank.acquire.bind_card_amount'));

        $qs = http_build_query(['orderId' => $card->generated_order_uuid]);
        $json = $this->be($self)->getJson('/clients/api/profile/credit-cards/link/success?' . $qs);
        $json->assertSuccessful();
        $this->assertDatabaseHas('client_credit_cards', [
            'uuid' => $card->uuid,
            'client_uuid' => $self->uuid,
            'generated_order_uuid' => $card->generated_order_uuid,
            'virtual_order_uuid' => $card->virtual_order_uuid,
            'card_mask' => $cardFake->card_mask,
            'binding_id' => $cardFake->binding_id,
        ]);

        $payment->refresh();
        $this->assertEquals(PaymentStatusEnum::REFUNDED, $payment->order_status);
    }

    /**
     *
     */
    public function testLinkCardError()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientCreditCard $card */
        $card = ClientCreditCard::factory()->createOne([
            'client_uuid' => $self->uuid,
            'card_mask' => null,
            'binding_id' => null,
        ]);

        /** @var ClientPayment $payment */
        $payment = ClientPayment::factory()->createOne([
            'client_uuid' => $self->uuid,
            'generated_order_uuid' => $card->generated_order_uuid,
            'related_reference_type' => ClientCreditCard::MORPH_TYPE_ALIAS,
            'related_reference_id' => $card->uuid
        ]);

        $acquireResolver = $this->createMock(AcquireResolverInterface::class);
        $this->app->instance(AcquireResolverInterface::class, $acquireResolver);
        $acquire = $this->createMock(AcquireInterface::class);

        $acquireResolver
            ->method('resolveByClientCard')
            ->willReturn($acquire);

        $externalStatus = $this->faker->uuid;
        $acquire
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with($card->generated_order_uuid)
            ->willReturn(new PaymentStatusDto(
                $externalStatus,
                PaymentStatusEnum::DECLINED,
                null,
                null
            ));

        $qs = http_build_query(['orderId' => $card->generated_order_uuid]);
        $json = $this->be($self)->getJson('/clients/api/profile/credit-cards/link/error?' . $qs);
        $json->assertSuccessful();
        $card->refresh();
        $this->assertNotNull($card->deleted_at);

        $payment->refresh();
        $this->assertEquals(PaymentStatusEnum::DECLINED, $payment->order_status);
        $this->assertEquals($externalStatus, $payment->external_status);
    }
}
