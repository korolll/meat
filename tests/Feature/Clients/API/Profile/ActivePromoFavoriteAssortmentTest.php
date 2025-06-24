<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Client;
use App\Models\ClientActivePromoFavoriteAssortment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class ActivePromoFavoriteAssortmentTest extends TestCaseNotificationsFake
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
        /** @var ClientActivePromoFavoriteAssortment $variant */
        $variant = ClientActivePromoFavoriteAssortment::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson("/clients/api/profile/active-promo-favorite-assortments");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [[
                'uuid' => $variant->uuid
            ]]
        ]);
    }
}
