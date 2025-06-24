<?php

namespace Tests\Feature\API\Profile;

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

        $self = $user;
        $json = $this->be($self)->getJson('/api/profile/loyalty-card-types');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $loyaltyCardType->uuid,
                ],
            ],
        ]);
    }
}
