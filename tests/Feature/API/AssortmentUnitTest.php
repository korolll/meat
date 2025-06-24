<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class AssortmentUnitTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $self = factory(User::class)->state('store')->create();
        $json = $this->be($self)->getJson('/api/assortment-units');

        $json->assertSuccessful()->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'short_name',
                ],
            ],
        ]);
    }
}
