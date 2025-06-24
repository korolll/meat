<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\LoyaltyCard;
use App\Models\LoyaltyCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
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

        Auth::shouldUse('api-clients');
    }

    /**
     * @test
     *
     * @testWith [false]
     *           [true]
     */
    public function testIndexNewCodes(bool $testExistOldCard)
    {
        $loyaltyCard = factory(LoyaltyCard::class)->state('owned')->create();
        Config::set('app.integrations.frontol.useCode', true);

        $self = $loyaltyCard->client;

        $oldNumber = null;
        if ($testExistOldCard) {
            $oldNumber = Str::uuid()->toString();
            LoyaltyCode::factory()->createOne([
                'client_uuid' => $self->uuid,
                'code' => $oldNumber,
                'expires_on' => now()->subDays(2),
            ]);
        }

        $json = $this->be($self)->getJson('/clients/api/profile/loyalty-cards');
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $loyaltyCard->uuid,
                ],
            ],
        ]);

        $number = $json->json('data.0.number');
        $this->assertNotEquals($oldNumber, $number);

        $this->assertDatabaseHas(LoyaltyCode::class, [
            'client_uuid' =>  $self->uuid,
            'code' => $number,
        ]);
    }

    /**
     * @return void
     */
    public function testIndexOldCode()
    {
        $loyaltyCard = factory(LoyaltyCard::class)->state('owned')->create();

        Config::set('app.integrations.frontol.useCode', true);

        $self = $loyaltyCard->client;

        $oldNumber = Str::uuid()->toString();
        LoyaltyCode::factory()->createOne([
            'client_uuid' => $self->uuid,
            'code' => $oldNumber,
            'expires_on' => now()->addDays(2),
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/loyalty-cards');
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $loyaltyCard->uuid,
                ],
            ],
        ]);

        $number = $json->json('data.0.number');
        $this->assertEquals($oldNumber, $number);
    }
}
