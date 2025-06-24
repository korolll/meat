<?php

namespace Tests\Feature\API;

use App\Models\PromoYellowPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;


class PromoYellowPriceTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @param string $userState
     *
     * @test
     * @testWith ["admin"]
     */
    public function store($userState)
    {
        $self = factory(User::class)->state($userState)->create();

        $data = factory(PromoYellowPrice::class)->make()->only([
            'assortment_uuid',
            'price',
            'start_at',
            'end_at',
            'is_enabled'
        ]);
        $store = factory(User::class)->state('store')->create();
        $data['store_uuids'] = [
            $store->uuid,
        ];
        $json = $this->be($self)->postJson('/api/promo-yellow-prices', $data);
        $json->assertSuccessful()->assertJsonFragment([
            'assortment_uuid' => $data['assortment_uuid'],
            'price' => $data['price'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
        ]);
        $this->assertDatabaseHas('promo_yellow_prices', [
            'assortment_uuid' => $data['assortment_uuid'],
            'price' => $data['price'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'is_enabled' => $data['is_enabled']
        ]);
        $this->assertDatabaseHas('promo_yellow_price_user', [
            'user_uuid' => $store->uuid,
        ]);
    }

    /**
     * @param string $userState
     *
     * @test
     * @testWith ["admin"]
     */
    public function update($userState)
    {
        $self = factory(User::class)->state($userState)->create();
        /** @var PromoYellowPrice $promo */
        $promo = factory(PromoYellowPrice::class)->state('has-store')->create([
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
        ]);

        $data = [
            'assortment_uuid' => $promo->assortment_uuid,
            'price' => $promo->price + 10,
            'is_enabled' => $promo->is_enabled,
            'start_at' => $promo->start_at,
            'end_at' => $promo->end_at,
            'store_uuids' => $promo->stores->map(fn(User $user) => $user->uuid)->toArray()
        ];

        $json = $this->be($self)->putJson("/api/promo-yellow-prices/{$promo->uuid}", $data);
        $json->assertSuccessful()->assertJsonFragment([
            'assortment_uuid' => $data['assortment_uuid'],
            'price' => $data['price'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
        ]);
        $this->assertDatabaseHas('promo_yellow_prices', [
            'assortment_uuid' => $data['assortment_uuid'],
            'price' => $data['price'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
        ]);
    }

    /**
     * @param string $userState
     *
     * @test
     * @testWith ["admin"]
     */
    public function show($userState)
    {
        $self = factory(User::class)->state($userState)->create();
        /** @var PromoYellowPrice $promo */
        $promo = factory(PromoYellowPrice::class)->state('has-store')->create([
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
        ]);

        $json = $this->be($self)->getJson("/api/promo-yellow-prices/{$promo->uuid}");
        $json->assertSuccessful()->assertJsonFragment([
            'assortment_uuid' => $promo->assortment_uuid,
            'price' => $promo->price,
            'start_at' => $promo->start_at,
            'end_at' => $promo->end_at,
            'uuid' => $promo->uuid
        ]);
    }

    /**
     * @param string $userState
     *
     * @test
     * @testWith ["admin"]
     */
    public function index($userState)
    {
        $self = factory(User::class)->state($userState)->create();
        /** @var PromoYellowPrice $promo */
        $promo = factory(PromoYellowPrice::class)->state('has-store')->create();

        $json = $this->be($self)->getJson("/api/promo-yellow-prices");
        $json->assertSuccessful()->assertJsonFragment([
            'assortment_uuid' => $promo->assortment_uuid,
            'price' => $promo->price,
            'start_at' => $promo->start_at,
            'end_at' => $promo->end_at,
            'uuid' => $promo->uuid
        ]);
    }

    /**
     * @param string $userState
     *
     * @test
     * @testWith ["admin"]
     */
    public function destroy($userState)
    {
        $self = factory(User::class)->state($userState)->create();
        /** @var PromoYellowPrice $promo */
        $promo = factory(PromoYellowPrice::class)->state('has-store')->create();

        $json = $this->be($self)->deleteJson("/api/promo-yellow-prices/{$promo->uuid}");
        $json->assertSuccessful()->assertJsonFragment([
            'assortment_uuid' => $promo->assortment_uuid,
            'price' => $promo->price,
            'start_at' => $promo->start_at,
            'end_at' => $promo->end_at,
            'uuid' => $promo->uuid
        ]);
    }

    /**
     * @param string $userState
     *
     * @test
     * @testWith ["admin"]
     */
    public function toggleEnable($userState)
    {
        $self = factory(User::class)->state($userState)->create();
        /** @var PromoYellowPrice $promo */
        $promo = factory(PromoYellowPrice::class)->state('has-store')->create();

        $json = $this->be($self)->postJson("/api/promo-yellow-prices/{$promo->uuid}/toggle-enable");
        $json->assertSuccessful()->assertJsonFragment([
            'assortment_uuid' => $promo->assortment_uuid,
            'price' => $promo->price,
            'start_at' => $promo->start_at,
            'end_at' => $promo->end_at,
            'uuid' => $promo->uuid,
            'is_enabled' => !$promo->is_enabled
        ]);
    }
}
