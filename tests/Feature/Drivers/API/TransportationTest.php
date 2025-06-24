<?php

namespace Tests\Feature\Drivers\API;

use App\Models\Transportation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class TransportationTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Auth::shouldUse('api-drivers');
    }

    /**
     * @test
     */
    public function index()
    {
        $transportation = factory(Transportation::class)->create();

        $self = $transportation->driver;
        $json = $this->be($self)->getJson('/drivers/api/transportations');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $transportation->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function setStarted()
    {
        $transportation = factory(Transportation::class)->create();

        $self = $transportation->driver;
        $json = $this->be($self)->putJson("/drivers/api/transportations/{$transportation->uuid}/set-started");

        $json->assertSuccessful();
    }
}
