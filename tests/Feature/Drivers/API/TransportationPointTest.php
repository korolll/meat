<?php

namespace Tests\Feature\Drivers\API;

use App\Models\ProductRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class TransportationPointTest extends TestCaseNotificationsFake
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
        $transportation = factory(ProductRequest::class)->state('has-transportation')->create()->transportation;

        $self = $transportation->driver;
        $json = $this->be($self)->getJson("/drivers/api/transportations/{$transportation->uuid}/points");

        $json->assertSuccessful()->assertJson([
            'data' => $transportation->transportationPoints->map->only('uuid')->all(),
        ]);
    }

    /**
     * @test
     */
    public function setArrived()
    {
        $transportation = factory(ProductRequest::class)->state('has-transportation')->create()->transportation;
        $point = $transportation->transportationPoints->first();

        $href = "/drivers/api/transportations/{$transportation->uuid}/points/{$point->uuid}/set-arrived";
        $json = $this->be($transportation->driver)->putJson($href);

        $json->assertSuccessful();
    }
}
