<?php

namespace Tests\Feature\Clients\API;

use App\Models\Client;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyCardType;
use App\Notifications\Clients\API\AuthenticationCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCaseNotificationsFake;

class AuthenticationTest extends TestCaseNotificationsFake
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
     * @test
     */
    public function loginViaPhone()
    {
        $client = factory(Client::class)->create();

        $json = $this->postJson('/clients/api/auth/login-via-phone', [
            'phone' => $client->phone,
        ]);

        $json->assertSuccessful()->assertJson([
            'message' => 'Authentication code was successfully sent',
        ]);

        $this->assertDatabaseHas('client_authentication_codes', [
            'client_uuid' => $client->uuid,
        ]);

        Notification::assertSentTo($client, AuthenticationCode::class);
        $code = Notification::sent($client, AuthenticationCode::class)->first()->code;

        $card = factory(LoyaltyCard::class)->create();
        $type = $card->loyalty_card_type_uuid;
        Config::set('app.clients.loyalty_card_types_for_generating', [$type]);

        $json = $this->postJson('/clients/api/auth/login-via-phone', [
            'phone' => $client->phone,
            'code' => $code,
        ]);

        $json->assertSuccessful()->assertJsonStructure([
            'token',
        ]);

        $this->assertDatabaseHas('client_authentication_tokens', [
            'client_uuid' => $client->uuid,
        ]);
        $card->refresh();
        $this->assertEquals($client->uuid, $card->client_uuid);
    }

    /**
     * @test
     */
    public function loginViaLoyalty()
    {
        $loyaltyCard = factory(LoyaltyCard::class)->state('owned')->create();

        $json = $this->postJson('/clients/api/auth/login-via-loyalty-card', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
        ]);

        $json->assertSuccessful()->assertJson([
            'message' => 'Authentication code was successfully sent',
        ]);

        $this->assertDatabaseHas('client_authentication_codes', [
            'client_uuid' => $loyaltyCard->client_uuid,
        ]);

        Notification::assertSentTo($loyaltyCard->client, AuthenticationCode::class);
        $code = Notification::sent($loyaltyCard->client, AuthenticationCode::class)->first()->code;

        $json = $this->postJson('/clients/api/auth/login-via-loyalty-card', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
            'code' => $code,
        ]);

        $json->assertSuccessful()->assertJsonStructure([
            'token',
        ]);

        $this->assertDatabaseHas('client_authentication_tokens', [
            'client_uuid' => $loyaltyCard->client_uuid,
        ]);
    }

    /**
     * @test
     */
    public function resetViaLoyalty()
    {
        $loyaltyCard = factory(LoyaltyCard::class)->state('owned')->create();

        $json = $this->postJson('/clients/api/auth/reset-via-loyalty-card', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
            'old_phone_last_four_digits' => substr($loyaltyCard->client->phone, -4),
            'new_phone' => '+79000000000',
        ]);

        $json->assertSuccessful()->assertJson([
            'message' => 'Authentication code was successfully sent',
        ]);

        $this->assertDatabaseHas('clients', [
            'uuid' => $loyaltyCard->client_uuid,
            'phone' => '+79000000000',
        ]);

        $this->assertDatabaseHas('client_authentication_codes', [
            'client_uuid' => $loyaltyCard->client_uuid,
        ]);

        Notification::assertSentTo($loyaltyCard->client, AuthenticationCode::class);
        $code = Notification::sent($loyaltyCard->client, AuthenticationCode::class)->first()->code;

        $json = $this->postJson('/clients/api/auth/login-via-loyalty-card', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'loyalty_card_number' => $loyaltyCard->number,
            'code' => $code,
        ]);

        $json->assertSuccessful()->assertJsonStructure([
            'token',
        ]);

        $this->assertDatabaseHas('client_authentication_tokens', [
            'client_uuid' => $loyaltyCard->client_uuid,
        ]);
    }
}
