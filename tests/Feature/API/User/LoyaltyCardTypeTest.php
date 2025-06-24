<?php

namespace Tests\Feature\API\User;

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
        $user = factory(User::class)->state('store')->create();
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();

        $user->loyaltyCardTypes()->attach($loyaltyCardType);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/users/{$user->uuid}/loyalty-card-types");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $loyaltyCardType->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $user = factory(User::class)->state('store')->create();
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson("/api/users/{$user->uuid}/loyalty-card-types", [
            'loyalty_card_type_uuid' => $loyaltyCardType->uuid,
        ]);

        $json->assertSuccessful();
        $this->assertDatabaseHas('loyalty_card_type_user', [
            'loyalty_card_type_uuid' => $loyaltyCardType->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $user = factory(User::class)->state('store')->create();
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();

        $user->loyaltyCardTypes()->attach($loyaltyCardType);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/users/{$user->uuid}/loyalty-card-types/{$loyaltyCardType->uuid}");

        $json->assertSuccessful();
        $this->assertDatabaseMissing('loyalty_card_type_user', [
            'loyalty_card_type_uuid' => $loyaltyCardType->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }
}
