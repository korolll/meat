<?php

namespace Tests\Feature\Integrations\CashRegisters\API;

use App\Models\Client;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyCardType;
use App\Models\User;
use App\Notifications\Clients\API\AuthenticationCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Tests\TestCaseNotificationsFake;

class LoyaltyCardTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Чтобы отключить проверку токена
        Config::set('app.integrations.cash-registers.token', null);
    }

    /**
     * @test
     */
    public function findButTokenIsInvalid()
    {
        Config::set('app.integrations.cash-registers.token', 'integration-token');

        $this->getJson(
            $this->makeFindUri(Str::uuid(), Str::uuid(), '1234567890')
        )
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function findButNoStoreExists()
    {
        $this->getJson(
            $this->makeFindUri(Str::uuid(), Str::uuid(), '1234567890')
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Specified store is not found');
    }

    /**
     * @test
     */
    public function findButNoLoyaltyCardTypeExists()
    {
        $user = factory(User::class)->state('store')->create();

        $this->getJson(
            $this->makeFindUri($user->uuid, Str::uuid(), '1234567890')
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Specified loyalty card type is not found');
    }

    /**
     * @test
     */
    public function findButLoyaltyCardNotAssociatedWithStore()
    {
        $user = factory(User::class)->state('store')->create();
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();

        $this->getJson(
            $this->makeFindUri($user->uuid, $loyaltyCardType->uuid, '1234567890')
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Specified loyalty card type is not associated with store');
    }

    /**
     * @test
     */
    public function findButNoLoyaltyCardExists()
    {
        $user = factory(User::class)->state('store')->create();
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();

        $user->loyaltyCardTypes()->attach($loyaltyCardType->uuid);

        $this->getJson(
            $this->makeFindUri($user->uuid, $loyaltyCardType->uuid, '1234567890')
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Specified loyalty card is not found');
    }

    /**
     * @test
     */
    public function findLoyaltyCardNotAssociatedWithSomeClient()
    {
        $user = factory(User::class)->state('store')->create();
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();
        $loyaltyCard = factory(LoyaltyCard::class)->create([
            'loyalty_card_type_uuid' => $loyaltyCardType->uuid,
        ]);

        $user->loyaltyCardTypes()->attach($loyaltyCardType->uuid);

        $this->getJson(
            $this->makeFindUri($user->uuid, $loyaltyCardType->uuid, $loyaltyCard->number)
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Specified loyalty card is not associated with some client');
    }

    /**
     * @test
     */
    public function find()
    {
        $user = factory(User::class)->state('store')->create();
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();
        $loyaltyCard = factory(LoyaltyCard::class)->state('owned')->create([
            'loyalty_card_type_uuid' => $loyaltyCardType->uuid,
        ]);

        $user->loyaltyCardTypes()->attach($loyaltyCardType->uuid);

        $this->getJson(
            $this->makeFindUri($user->uuid, $loyaltyCardType->uuid, $loyaltyCard->number)
        )
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'uuid' => $loyaltyCard->uuid,
                    'discount_percent' => $loyaltyCard->discount_percent,
                ],
            ]);
    }

    /**
     * @test
     */
    public function associateButAuthenticationCodeSent()
    {
        $loyaltyCard = factory(LoyaltyCard::class)->create();
        $client = factory(Client::class)->make();

        $json = $this->postJson('/integrations/cash-registers/api/loyalty-cards/associate', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
            'phone' => $client->phone,
        ]);

        $json->assertSuccessful()->assertJson([
            'message' => 'Authentication code was successfully sent',
        ]);

        // Метод должен был создать нового клиента, он-то нам и нужен для дальнейших проверок
        $this->assertNotNull(
            ($client = Client::where('phone', $client->phone)->first())
        );

        Notification::assertSentTo($client, AuthenticationCode::class);
        $code = Notification::sent($client, AuthenticationCode::class)->first()->code;

        $this->assertDatabaseHas('client_authentication_codes', [
            'code' => $code,
        ]);

        return [$client, $code];
    }

    /**
     * @test
     */
    public function associateButAuthenticationCodeIsInvalid()
    {
        $loyaltyCard = factory(LoyaltyCard::class)->create();
        $client = factory(Client::class)->make();

        $this->postJson('/integrations/cash-registers/api/loyalty-cards/associate', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
            'phone' => $client->phone,
            'code' => 1234,
        ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Invalid authentication code');
    }

    /**
     * @test
     */
    public function associateButLoyaltyCardTypeIsNotFound()
    {
        [$client, $code] = $this->associateButAuthenticationCodeSent();

        $loyaltyCard = factory(LoyaltyCard::class)->create();

        $this->postJson('/integrations/cash-registers/api/loyalty-cards/associate', [
            'loyalty_card_type_uuid' => Uuid::NIL,
            'loyalty_card_number' => $loyaltyCard->number,
            'phone' => $client->phone,
            'code' => $code,
        ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Specified loyalty card type is not found');
    }

    /**
     * @test
     */
    public function associateButClientIsAlreadyAssociatedWithLoyaltyCardOfSameType()
    {
        [$client, $code] = $this->associateButAuthenticationCodeSent();

        $loyaltyCard = factory(LoyaltyCard::class)->create([
            'client_uuid' => $client->uuid,
        ]);

        $this->postJson('/integrations/cash-registers/api/loyalty-cards/associate', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
            'phone' => $client->phone,
            'code' => $code,
        ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Specified client is already associated with loyalty card of same type');
    }

    /**
     * @test
     */
    public function associateBuyLoyaltyCardIsNotFound()
    {
        [$client, $code] = $this->associateButAuthenticationCodeSent();

        $loyaltyCard = factory(LoyaltyCard::class)->create();

        $this->postJson('/integrations/cash-registers/api/loyalty-cards/associate', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => 1234567890,
            'phone' => $client->phone,
            'code' => $code,
        ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Specified loyalty card is not found');
    }

    /**
     * @test
     */
    public function associateButLoyaltyCardIsAlreadyAssociatedWithSomeClient()
    {
        [$client, $code] = $this->associateButAuthenticationCodeSent();

        $someOtherClient = factory(Client::class)->create();

        $loyaltyCard = factory(LoyaltyCard::class)->create([
            'client_uuid' => $someOtherClient->uuid,
        ]);

        $this->postJson('/integrations/cash-registers/api/loyalty-cards/associate', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
            'phone' => $client->phone,
            'code' => $code,
        ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee('Specified loyalty card is already associated with some client');
    }

    /**
     * @test
     */
    public function associate()
    {
        [$client, $code] = $this->associateButAuthenticationCodeSent();

        $loyaltyCard = factory(LoyaltyCard::class)->create();

        $json = $this->postJson('/integrations/cash-registers/api/loyalty-cards/associate', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
            'phone' => $client->phone,
            'code' => $code,
        ]);

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $loyaltyCard->uuid,
                'discount_percent' => $loyaltyCard->discount_percent,
            ],
        ]);

        $this->assertDatabaseHas('loyalty_cards', [
            'uuid' => $loyaltyCard->uuid,
            'client_uuid' => $client->uuid,
        ]);
    }

    /**
     * @test
     */
    public function associateWithClientName()
    {
        [$client, $code] = $this->associateButAuthenticationCodeSent();

        $loyaltyCard = factory(LoyaltyCard::class)->create();
        $newClient = factory(Client::class)->make();

        $json = $this->postJson('/integrations/cash-registers/api/loyalty-cards/associate', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
            'phone' => $client->phone,
            'code' => $code,
            'client_name' => $newClient->name,
        ]);

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $loyaltyCard->uuid,
                'discount_percent' => $loyaltyCard->discount_percent,
            ],
        ]);

        $this->assertDatabaseHas('loyalty_cards', [
            'uuid' => $loyaltyCard->uuid,
            'client_uuid' => $client->uuid,
        ]);
        $this->assertDatabaseHas('clients', [
            'uuid' => $client->uuid,
            'name' => $newClient->name,
        ]);
    }

    /**
     * @param string $userUuid
     * @param string $loyaltyCardTypeUuid
     * @param string $loyaltyCardNumber
     * @return string
     */
    protected function makeFindUri(string $userUuid, string $loyaltyCardTypeUuid, string $loyaltyCardNumber): string
    {
        $query = http_build_query([
            'user_uuid' => $userUuid,
            'loyalty_card_type_uuid' => $loyaltyCardTypeUuid,
            'loyalty_card_number' => $loyaltyCardNumber,
        ]);

        return '/integrations/cash-registers/api/loyalty-cards/find' . '?' . $query;
    }
}
