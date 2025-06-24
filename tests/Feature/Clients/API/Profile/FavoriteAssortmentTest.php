<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class FavoriteAssortmentTest extends TestCaseNotificationsFake
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
    public function store()
    {
        $assortment = factory(Assortment::class)->create();
        $self = factory(Client::class)->create();

        $json = $this->be($self)->postJson('/clients/api/profile/favorite-assortments', [
            'assortment_uuid' => $assortment->uuid,
        ]);

        $json->assertSuccessful();
        $this->assertDatabaseHas('assortment_client_favorites', [
            'client_uuid' => $self->uuid,
            'assortment_uuid' => $assortment->uuid,
        ]);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $assortment = factory(Assortment::class)->create();
        $self = factory(Client::class)->create();

        $self->favoriteAssortments()->attach($assortment);

        $json = $this->be($self)->deleteJson("/clients/api/profile/favorite-assortments/{$assortment->uuid}");

        $json->assertSuccessful();
        $this->assertDatabaseMissing('assortment_client_favorites', [
            'client_uuid' => $self->uuid,
            'assortment_uuid' => $assortment->uuid,
        ]);
    }
}
