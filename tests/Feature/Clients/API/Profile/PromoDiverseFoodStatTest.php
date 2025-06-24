<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Client;
use App\Models\PromoDiverseFoodClientStat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class PromoDiverseFoodStatTest extends TestCaseNotificationsFake
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
        /** @var PromoDiverseFoodClientStat $stat */
        $stat = PromoDiverseFoodClientStat::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson("/clients/api/profile/promo-diverse-food-stats");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [[
                'uuid' => $stat->uuid
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
        /** @var PromoDiverseFoodClientStat $stat */
        $stat = PromoDiverseFoodClientStat::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson("/clients/api/profile/promo-diverse-food-stats/{$stat->uuid}");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [
                'uuid' => $stat->uuid
            ]
        ]);
    }
}
