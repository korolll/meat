<?php

namespace Tests\Feature\API;

use App\Models\Driver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class DriverTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $driver = factory(Driver::class)->create();

        $self = $driver->user;
        $json = $this->be($self)->getJson('/api/profile/drivers');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $driver->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function store()
    {
        $password = \Str::random(random_int(8, 50));
        $driver = factory(Driver::class)->make([
            'password' => $password,
        ]);

        $self = $driver->user;
        $requestData = $driver->only([
            'full_name',
            'email',
            'hired_on',
            'fired_on',
            'comment',
            'license_number',
        ]);
        $requestData['password'] = $password;

        $json = $this->be($self)->postJson('/api/profile/drivers', $requestData);

        $data = [
            'email' => $driver->email,
        ];
        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('drivers', $data);
    }

    /**
     * @test
     */
    public function show()
    {
        $driver = factory(Driver::class)->create();

        $self = $driver->user;
        $json = $this->be($self)->getJson("/api/profile/drivers/{$driver->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $driver->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $driverOld = factory(Driver::class)->create();
        $driverNew = factory(Driver::class)->make([
            'email' => $driverOld->email,
        ]);

        $self = $driverOld->user;
        $json = $this->be($self)->putJson("/api/profile/drivers/{$driverOld->uuid}", $driverNew->only([
            'full_name',
            'email',
            'password',
            'hired_on',
            'fired_on',
            'comment',
            'license_number',
        ]));

        $data = [
            'uuid' => $driverOld->uuid,
            'full_name' => $driverNew->full_name,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('drivers', $data);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $driver = factory(Driver::class)->create();

        $self = $driver->user;
        $json = $this->be($self)->deleteJson("/api/profile/drivers/{$driver->uuid}");

        $json->assertSuccessful();
    }
}
