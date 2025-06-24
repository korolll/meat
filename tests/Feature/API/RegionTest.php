<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class RegionTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/regions');

        $json->assertSuccessful()->assertJsonStructure([
            'data' => [
                '0' => [
                    'uuid',
                    'name',
                ],
            ],
        ]);
    }
}
