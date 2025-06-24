<?php

namespace Tests\Feature\API;

use App\Models\PromoDiverseFoodSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;


class PromoDiverseFoodSettingsTest extends TestCaseNotificationsFake
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
        /** @var PromoDiverseFoodSettings $promo */
        $promo = factory(PromoDiverseFoodSettings::class)->make();

        $except = $promo->only([
            'count_purchases',
            'count_rating_scores',
            'discount_percent',
            'is_enabled',
        ]);
        $json = $this->be($self)->postJson('/api/promo-diverse-food-settings', $except);

        $json->assertSuccessful()->assertJsonFragment($except);
        $this->assertDatabaseHas('promo_diverse_food_settings', $except);
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
        /** @var PromoDiverseFoodSettings $promo */
        $promo = factory(PromoDiverseFoodSettings::class)->create();

        $data = $promo->only([
            'count_purchases',
            'count_rating_scores',
            'discount_percent',
            'is_enabled',
        ]);
        $data['count_purchases'] += 13;

        $json = $this->be($self)->putJson("/api/promo-diverse-food-settings/{$promo->uuid}", $data);
        $json->assertSuccessful()->assertJsonFragment($data);
        $this->assertDatabaseHas('promo_diverse_food_settings', $data);
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
        /** @var PromoDiverseFoodSettings $promo */
        $promo = factory(PromoDiverseFoodSettings::class)->create();

        $json = $this->be($self)->getJson("/api/promo-diverse-food-settings/{$promo->uuid}");
        $json->assertSuccessful()->assertJsonFragment($promo->only([
            'count_purchases',
            'count_rating_scores',
            'discount_percent',
            'is_enabled',
        ]));
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
        /** @var PromoDiverseFoodSettings $promo */
        $promo = factory(PromoDiverseFoodSettings::class)->create();

        $json = $this->be($self)->getJson("/api/promo-diverse-food-settings");
        $json->assertSuccessful()->assertJsonFragment($promo->only([
            'count_purchases',
            'count_rating_scores',
            'discount_percent',
            'is_enabled',
        ]));
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
        /** @var PromoDiverseFoodSettings $promo */
        $promo = factory(PromoDiverseFoodSettings::class)->create();

        $json = $this->be($self)->deleteJson("/api/promo-diverse-food-settings/{$promo->uuid}");
        $json->assertSuccessful()->assertJsonFragment($promo->only([
            'count_purchases',
            'count_rating_scores',
            'discount_percent',
            'is_enabled',
        ]));
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
        /** @var PromoDiverseFoodSettings $promo */
        $promo = factory(PromoDiverseFoodSettings::class)->create();

        $json = $this->be($self)->postJson("/api/promo-diverse-food-settings/{$promo->uuid}/toggle-enable");
        $json->assertSuccessful()->assertJsonFragment([
            'is_enabled' => !$promo->is_enabled
        ]);
    }
}
