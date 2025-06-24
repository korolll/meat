<?php

namespace Tests\Feature\API;

use App\Jobs\ResolveClientFavoriteAssortmentVariantJob;
use App\Models\PromoDiverseFoodSettings;
use App\Models\PromoFavoriteAssortmentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;


class PromoFavoriteAssortmentSettingsTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testStore()
    {
        $self = factory(User::class)->state('admin')->create();
        /** @var PromoDiverseFoodSettings $promo */
        $promo = PromoFavoriteAssortmentSetting::factory()->makeOne();

        $data = $promo->only([
            'threshold_amount',
            'number_of_sum_days',
            'number_of_active_days',
            'discount_percent',
            'is_enabled',
        ]);
        $json = $this->be($self)->postJson('/api/promo-favorite-assortment-settings', $data);

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas($promo->getTable(), $data);
    }

    /**
     *
     */
    public function testUpdate()
    {

        $self = factory(User::class)->state('admin')->create();
        /** @var PromoDiverseFoodSettings $promo */
        $promo = PromoFavoriteAssortmentSetting::factory()->createOne();
        /** @var PromoDiverseFoodSettings $newPromo */
        $newPromo = PromoFavoriteAssortmentSetting::factory()->makeOne();

        $data = $newPromo->only([
            'threshold_amount',
            'number_of_sum_days',
            'number_of_active_days',
            'discount_percent',
            'is_enabled',
        ]);

        $this->expectsJobs(ResolveClientFavoriteAssortmentVariantJob::class);
        $json = $this->be($self)->putJson("/api/promo-favorite-assortment-settings/{$promo->uuid}", $data);
        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas($promo->getTable(), $data);
    }

    /**
     *
     */
    public function testShow()
    {
        $self = factory(User::class)->state('admin')->create();
        /** @var PromoDiverseFoodSettings $promo */
        $promo = PromoFavoriteAssortmentSetting::factory()->createOne();

        $json = $this->be($self)->getJson("/api/promo-favorite-assortment-settings/{$promo->uuid}");
        $json->assertSuccessful()->assertJsonFragment($promo->only([
            'threshold_amount',
            'number_of_sum_days',
            'number_of_active_days',
            'discount_percent',
            'is_enabled',
        ]));
    }

    /**
     *
     */
    public function testIndex()
    {
        $self = factory(User::class)->state('admin')->create();
        /** @var PromoDiverseFoodSettings $promo */
        $promo = PromoFavoriteAssortmentSetting::factory()->createOne();

        $json = $this->be($self)->getJson("/api/promo-favorite-assortment-settings");
        $json->assertSuccessful()->assertJsonFragment($promo->only([
            'threshold_amount',
            'number_of_sum_days',
            'number_of_active_days',
            'discount_percent',
            'is_enabled',
        ]));
    }

    /**
     *
     */
    public function testToggleEnable()
    {
        $self = factory(User::class)->state('admin')->create();
        /** @var PromoDiverseFoodSettings $promo */
        $promo = PromoFavoriteAssortmentSetting::factory()->createOne();

        $json = $this->be($self)->postJson("/api/promo-favorite-assortment-settings/{$promo->uuid}/toggle-enable");
        $json->assertSuccessful()->assertJsonFragment([
            'is_enabled' => !$promo->is_enabled
        ]);
    }
}
