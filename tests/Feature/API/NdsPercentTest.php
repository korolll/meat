<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class NdsPercentTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/nds-percents');

        $json->assertSuccessful()->assertJson([
            'data' => config('app.nds-percents'),
        ]);
    }
}
