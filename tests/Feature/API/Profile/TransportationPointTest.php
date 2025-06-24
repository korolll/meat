<?php

namespace Tests\Feature\API;

use App\Models\ProductRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class TransportationPointTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $transportation = factory(ProductRequest::class)->state('has-transportation')->create()->transportation;

        $self = $transportation->user;
        $json = $this->be($self)->getJson("/api/profile/transportations/{$transportation->uuid}/points");

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
        $arrivedAt = now();

        $href = "/api/profile/transportations/{$transportation->uuid}/points/{$point->uuid}/set-arrived";
        $json = $this->be($transportation->user)->putJson($href, [
            'arrived_at' => $arrivedAt,
        ]);

        $data = [
            'uuid' => $point->uuid,
            'arrived_at' => $arrivedAt,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('transportation_points', $data);
    }

    /**
     * @test
     */
    public function setOrder()
    {
        $transportation = factory(ProductRequest::class)->state('has-transportation')->create()->transportation;

        $self = $transportation->user;
        $json = $this->be($self)->putJson("/api/profile/transportations/{$transportation->uuid}/points/set-order", [
            'transportation_points' => [
                ['uuid' => $transportation->transportationPoints[0]->uuid],
                ['uuid' => $transportation->transportationPoints[1]->uuid],
            ],
        ]);

        $json->assertSuccessful();
    }
}
