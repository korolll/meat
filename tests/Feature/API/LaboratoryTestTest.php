<?php

namespace Tests\Feature\API;

use App\Models\LaboratoryTest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class LaboratoryTestTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $self = factory(User::class)->state('laboratory')->create();
        $laboratoryTest = factory(LaboratoryTest::class)->state('new')->create();

        $json = $this->be($self)->getJson('/api/laboratory-tests');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $laboratoryTest->uuid,
                ]
            ],
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $self = factory(User::class)->state('laboratory')->create();
        $laboratoryTest = factory(LaboratoryTest::class)->state('new')->create();
        $json = $this->be($self)->getJson("/api/laboratory-tests/{$laboratoryTest->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $laboratoryTest->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function setInWork()
    {
        $self = factory(User::class)->state('laboratory')->create();
        $laboratoryTest = factory(LaboratoryTest::class)->state('new')->create();

        $json = $this->be($self)->putJson("/api/laboratory-tests/{$laboratoryTest->uuid}/set-in-work");
        $json->assertSuccessful();
    }
}
