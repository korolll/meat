<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Client;
use App\Models\LoyaltyCardType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCaseNotificationsFake;

class FavoriteStoreTest extends TestCaseNotificationsFake
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
        $store = factory(User::class)->state('store')->create();
        $self = factory(Client::class)->create();
        $json = $this->be($self)->postJson('/clients/api/profile/favorite-stores', [
            'store_uuid' => $store->uuid
        ]);

        $json->assertSuccessful();
        $this->assertDatabaseHas('client_user_favorites', [
            'user_uuid' => $store->uuid,
            'client_uuid' => $self->uuid
        ]);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $store = factory(User::class)->state('store')->create();
        /** @var Client $self */
        $self = factory(Client::class)->create();
        // Избранный магаз
        $self->favoriteStores()->attach($store->uuid);
        $json = $this->be($self)->deleteJson('/clients/api/profile/favorite-stores/' . urlencode($store->uuid));

        $json->assertSuccessful();
        $this->assertDatabaseMissing('client_user_favorites', [
            'user_uuid' => $store->uuid,
            'client_uuid' => $self->uuid
        ]);
    }
}
