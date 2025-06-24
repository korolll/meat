<?php

namespace Tests\Feature\API;

use App\Models\LoyaltyCardType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class LoyaltyCardTypeTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/loyalty-card-types?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $loyaltyCardType->uuid,
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $loyaltyCardType = factory(LoyaltyCardType::class)->make();

        $data = $loyaltyCardType->only([
            'name',
            'logo_file_uuid',
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/loyalty-card-types', $data);

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('loyalty_card_types', $data);
    }

    /**
     * @test
     */
    public function show()
    {
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/loyalty-card-types/{$loyaltyCardType->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $loyaltyCardType->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $loyaltyCardTypeOld = factory(LoyaltyCardType::class)->create();
        $loyaltyCardTypeNew = factory(LoyaltyCardType::class)->make();

        $data = $loyaltyCardTypeNew->only([
            'name',
            'logo_file_uuid',
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/loyalty-card-types/{$loyaltyCardTypeOld->uuid}", $data);

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('loyalty_card_types', $data);
    }
}
