<?php

namespace Tests\Feature\Clients\API;

use App\Models\Client;
use App\Models\PromoDescription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class PromoDescriptionTest extends TestCaseNotificationsFake
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
    public function index()
    {
        /** @var PromoDescription $promoDescription */
        $promoDescription = factory(PromoDescription::class)->create();

        $self = factory(Client::class)->create();
        $json = $this->be($self)->getJson('/clients/api/promo-descriptions?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $promoDescription->uuid,
        ]);
    }
}
