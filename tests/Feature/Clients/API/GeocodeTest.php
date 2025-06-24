<?php

namespace Tests\Feature\Clients\API;

use App\Models\Client;
use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Location;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class GeocodeTest extends TestCaseNotificationsFake
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
    public function testGeocode()
    {
        $self = factory(Client::class)->create();
        $address = $this->faker->address;

        $geocodedFakeId = $this->faker->uuid;

        $geocoded = $this->createMock(Location::class);
        $geocoded
            ->method('toArray')
            ->willReturn(['id' => $geocodedFakeId]);

        $provider = $this->createMock(ProviderAndDumperAggregator::class);
        $provider
            ->method('get')
            ->willReturn(collect([$geocoded]));
        Geocoder::partialMock()
            ->shouldReceive('geocodeQuery')
            ->andReturn($provider);

        $json = $this->be($self)->getJson('/clients/api/geocode?address=' . urlencode($address));
        $json->assertSuccessful()->assertJson([
            'data' => [[
                'id' => $geocodedFakeId
            ]]
        ]);
    }
}
