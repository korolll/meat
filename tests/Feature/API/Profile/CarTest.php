<?php

namespace Tests\Feature\API;

use App\Models\Car;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class CarTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $car = factory(Car::class)->create();

        $self = $car->user;
        $json = $this->be($self)->getJson('/api/profile/cars');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $car->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $car = factory(Car::class)->make();

        $self = $car->user;
        $json = $this->be($self)->postJson('/api/profile/cars', $car->only([
            'brand_name',
            'model_name',
            'license_plate',
            'call_sign',
            'max_weight',
            'is_active',
        ]));

        $data = [
            'uuid' => $json->json('data.uuid'),
            'model_name' => $car->model_name,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('cars', $data);
    }

    /**
     * @test
     */
    public function show()
    {
        $car = factory(Car::class)->create();

        $self = $car->user;
        $json = $this->be($self)->getJson("/api/profile/cars/{$car->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $car->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $carOld = factory(Car::class)->create();
        $carNew = factory(Car::class)->make();

        $self = $carOld->user;
        $json = $this->be($self)->putJson("/api/profile/cars/{$carOld->uuid}", $carNew->only([
            'brand_name',
            'model_name',
            'license_plate',
            'call_sign',
            'max_weight',
            'is_active',
        ]));

        $data = [
            'uuid' => $carOld->uuid,
            'brand_name' => $carNew->brand_name,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('cars', $data);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $car = factory(Car::class)->create();

        $self = $car->user;
        $json = $this->be($self)->deleteJson("/api/profile/cars/{$car->uuid}");

        $json->assertSuccessful();
    }
}
