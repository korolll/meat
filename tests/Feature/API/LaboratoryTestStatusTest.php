<?php

namespace Tests\Feature\API;

use App\Models\LaboratoryTestStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class LaboratoryTestStatusTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/laboratory-test-statuses');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'id' => LaboratoryTestStatus::ID_CREATED,
                ],
                [
                    'id' => LaboratoryTestStatus::ID_NEW,
                ],
            ],
        ]);
    }

    /**
     * @test
     * @testWith ["canceled"]
     */
    public function show($id)
    {
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/laboratory-test-statuses/{$id}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'id' => $id,
            ],
        ]);
    }
}
