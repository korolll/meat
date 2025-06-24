<?php

namespace Tests\Feature\Clients\API;

use App\Models\Client;
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
    public function testIndex()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var PromoDiverseFoodSettings $setting */
        $setting = factory(PromoDiverseFoodSettings::class)->create([
            'is_enabled' => true
        ]);

        $json = $this->be($self)->getJson("/clients/api/promo-diverse-food-settings");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [[
                'uuid' => $setting->uuid
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
        /** @var PromoDiverseFoodSettings $setting */
        $setting = factory(PromoDiverseFoodSettings::class)->create([
            'is_enabled' => true
        ]);

        $json = $this->be($self)->getJson("/clients/api/promo-diverse-food-settings/{$setting->uuid}");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [
                'uuid' => $setting->uuid
            ]
        ]);
    }
}
