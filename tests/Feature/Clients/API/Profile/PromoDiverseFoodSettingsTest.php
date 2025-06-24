<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Client;
use App\Models\PromoDiverseFoodClientStat;
use App\Models\PromoDiverseFoodSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class PromoDiverseFoodSettingsTest extends TestCaseNotificationsFake
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
    public function testFutureLevel()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        PromoDiverseFoodClientStat::factory()->createOne([
            'client_uuid' => $self->uuid,
            'purchased_count' => 5,
            'rated_count' => 5,
            'month' => now()->format('Y-m')
        ]);

        factory(PromoDiverseFoodSettings::class)->create([
            'count_purchases' => 10,
            'count_rating_scores' => 5,
            'discount_percent' => 10,
            'is_enabled' => true
        ]);

        /** @var \App\Models\PromoDiverseFoodSettings $setting */
        $setting = factory(PromoDiverseFoodSettings::class)->create([
            'count_purchases' => 4,
            'count_rating_scores' => 4,
            'discount_percent' => 5,
            'is_enabled' => true
        ]);

        $json = $this->be($self)->getJson("/clients/api/profile/promo-diverse-food-settings/future-level");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [
                'uuid' => $setting->uuid
            ]
        ]);
    }

    /**
     *
     */
    public function testFutureLevelFirst()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();

        factory(PromoDiverseFoodSettings::class)->create([
            'count_purchases' => 10,
            'count_rating_scores' => 5,
            'discount_percent' => 10,
            'is_enabled' => true
        ]);

        factory(PromoDiverseFoodSettings::class)->create([
            'count_purchases' => 4,
            'count_rating_scores' => 4,
            'discount_percent' => 5,
            'is_enabled' => true
        ]);

        $json = $this->be($self)->getJson("/clients/api/profile/promo-diverse-food-settings/future-level");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => null
        ]);
    }
}
