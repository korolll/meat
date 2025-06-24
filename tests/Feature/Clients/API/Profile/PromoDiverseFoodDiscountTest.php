<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Client;
use App\Models\PromoDiverseFoodClientDiscount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class PromoDiverseFoodDiscountTest extends TestCaseNotificationsFake
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
        /** @var PromoDiverseFoodClientDiscount $discount */
        $discount = PromoDiverseFoodClientDiscount::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson("/clients/api/profile/promo-diverse-food-discounts");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [[
                'uuid' => $discount->uuid
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
        /** @var PromoDiverseFoodClientDiscount $discount */
        $discount = PromoDiverseFoodClientDiscount::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson("/clients/api/profile/promo-diverse-food-discounts/{$discount->uuid}");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [
                'uuid' => $discount->uuid
            ]
        ]);
    }
}
