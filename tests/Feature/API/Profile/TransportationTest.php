<?php

namespace Tests\Feature\API;

use App\Models\ProductRequest;
use App\Models\Transportation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class TransportationTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $transportation = factory(Transportation::class)->create();

        $self = $transportation->user;
        $json = $this->be($self)->getJson('/api/profile/transportations');

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
    public function store()
    {
        $transportation = factory(Transportation::class)->make();
        $productRequest = factory(ProductRequest::class)->create([
            'delivery_user_uuid' => $transportation->user_uuid,
        ]);

        $self = $transportation->user;
        $json = $this->be($self)->postJson('/api/profile/transportations', [
            'date' => $transportation->date,
            'car_uuid' => $transportation->car_uuid,
            'driver_uuid' => $transportation->driver_uuid,
            'product_requests' => [
                ['uuid' => $productRequest->uuid],
            ],
        ]);

        $data = [
            'car_uuid' => $transportation->car_uuid,
            'driver_uuid' => $transportation->driver_uuid,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('transportations', $data);
        $this->assertDatabaseHas('transportation_points', [
            'product_request_uuid' => $productRequest->uuid,
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $transportation = factory(Transportation::class)->create();

        $self = $transportation->user;
        $json = $this->be($self)->getJson("/api/profile/transportations/{$transportation->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $transportation->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $transportationOld = factory(Transportation::class)->create();
        $transportationNew = factory(Transportation::class)->make();

        $self = $transportationOld->user;
        $json = $this->be($self)->putJson("/api/profile/transportations/{$transportationOld->uuid}", [
            'date' => $transportationNew->date,
            'car_uuid' => $transportationNew->car_uuid,
            'driver_uuid' => $transportationNew->driver_uuid,
        ]);

        $data = [
            'car_uuid' => $transportationNew->car_uuid,
            'driver_uuid' => $transportationNew->driver_uuid,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('transportations', $data);
    }

    /**
     * @test
     */
    public function setStarted()
    {
        $transportation = factory(Transportation::class)->create();
        $startedAt = now();

        $self = $transportation->user;
        $json = $this->be($self)->putJson("/api/profile/transportations/{$transportation->uuid}/set-started", [
            'started_at' => $startedAt,
        ]);

        $data = [
            'uuid' => $transportation->uuid,
            'started_at' => $startedAt,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('transportations', $data);
    }
}
