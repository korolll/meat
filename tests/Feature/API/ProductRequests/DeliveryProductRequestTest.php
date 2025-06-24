<?php

namespace Tests\Feature\API\ProductRequests;

use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class DeliveryProductRequestTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $request = factory(ProductRequest::class)->state('waiting-for-delivery')->create();

        $self = factory(User::class)->state('delivery-service')->create();
        $json = $this->be($self)->getJson('/api/product-requests/delivery?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $request->uuid,
        ]);
    }
}
