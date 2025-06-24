<?php

namespace Tests\Feature\API;

use App\Models\LoyaltyCard;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class LoyaltyCardTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $loyaltyCard = factory(LoyaltyCard::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/loyalty-cards/'.$loyaltyCard->uuid);

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $loyaltyCard->uuid,
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $loyaltyCard = factory(LoyaltyCard::class)->make();

        $data = $loyaltyCard->only([
            'loyalty_card_type_uuid',
            'number',
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/loyalty-cards', $data);

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('loyalty_cards', $data);
    }

    /**
     * @test
     */
    public function show()
    {
        $loyaltyCard = factory(LoyaltyCard::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/loyalty-cards/{$loyaltyCard->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $loyaltyCard->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $loyaltyCardOld = factory(LoyaltyCard::class)->create();
        $loyaltyCardNew = factory(LoyaltyCard::class)->make();

        $data = $loyaltyCardNew->only([
            'loyalty_card_type_uuid',
            'number',
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/loyalty-cards/{$loyaltyCardOld->uuid}", $data);

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('loyalty_cards', $data);
    }
}
