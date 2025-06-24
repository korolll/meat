<?php

namespace Tests\Feature\API;

use App\Models\PromoDescription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class PromoDescriptionTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        /** @var PromoDescription $promoDescription */
        $promoDescription = factory(PromoDescription::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/promo-descriptions?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $promoDescription->uuid,
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        /** @var PromoDescription $promoDescription */
        $promoDescription = factory(PromoDescription::class)->make();

        $data = $promoDescription->only([
            'name',
            'logo_file_uuid',
            'title',
            'description',
            'is_hidden',
            'color',
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/promo-descriptions', $data);

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('promo_descriptions', $data);
    }

    /**
     * @test
     */
    public function show()
    {
        /** @var PromoDescription $promoDescription */
        $promoDescription = factory(PromoDescription::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/promo-descriptions/{$promoDescription->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $promoDescription->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        /** @var PromoDescription $promoDescriptionOld */
        $promoDescriptionOld = factory(PromoDescription::class)->create();
        /** @var PromoDescription $promoDescriptionNew */
        $promoDescriptionNew = factory(PromoDescription::class)->make();

        $data = $promoDescriptionNew->only([
            'name',
            'logo_file_uuid',
            'title',
            'description',
            'is_hidden',
            'color',
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/promo-descriptions/{$promoDescriptionOld->uuid}", $data);

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('promo_descriptions', $data);
    }
}
