<?php

namespace Tests\Feature\Clients\API;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\RatingScore;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class RatingScoreTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Auth::shouldUse('api-clients');
    }

    /**
     * @test
     */
    public function findForAssortmentsByClients()
    {
        $assortment = factory(Assortment::class)->create();
        $client = factory(Client::class)->create();
        $comment = null;

        factory(RatingScore::class)->create([
            'rated_reference_type' => Assortment::MORPH_TYPE_ALIAS,
            'rated_reference_id' => $assortment->uuid,
            'rated_by_reference_type' => Client::MORPH_TYPE_ALIAS,
            'rated_by_reference_id' => $client->uuid,
            'rated_through_reference_type' => $client::MORPH_TYPE_ALIAS,
            'rated_through_reference_id' => $client->uuid,
            'additional_attributes->comment' => $comment
        ]);

        $filterQuery = http_build_query([
            'where' => [
                ['assortment_uuid', $assortment->uuid],
                ['client_uuid', $client->uuid],
                ['comment', 'is null']
            ],
            'order_by' => [
                'value' => 'desc',
                'created_at' => 'asc'
            ],
        ]);

        $self = factory(Client::class)->create();
        $json = $this->be($self)->getJson("/clients/api/rating-scores/assortments/clients?{$filterQuery}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'assortment_uuid' => $assortment->uuid,
                    'assortment_name' => $assortment->name,
                    'client_uuid' => $client->uuid,
                    'client_name' => $client->name,
                    'comment' => $comment
                ],
            ],
        ]);
    }
}
