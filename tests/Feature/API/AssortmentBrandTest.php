<?php

namespace Tests\Feature\API;

use App\Models\Assortment;
use App\Models\AssortmentBrand;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCaseNotificationsFake;

class AssortmentBrandTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $assortmentBrand = factory(AssortmentBrand::class)->create();

        $self = factory(User::class)->create();
        $json = $this->be($self)->getJson('/api/assortment-brands?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            [
                'uuid' => $assortmentBrand->uuid,
                'name' => $assortmentBrand->name,
                'created_at' => $assortmentBrand->created_at,
            ],
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $assortmentBrand = factory(AssortmentBrand::class)->make();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/assortment-brands', $assortmentBrand->only([
            'name',
        ]));

        $data = [
            'uuid' => $json->json('data.uuid'),
            'name' => $assortmentBrand->name,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('assortment_brands', $data);
    }

    /**
     * @test
     */
    public function show()
    {
        $assortmentBrand = factory(AssortmentBrand::class)->create();

        $self = factory(User::class)->create();
        $json = $this->be($self)->getJson("/api/assortment-brands/{$assortmentBrand->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $assortmentBrand->uuid,
                'name' => $assortmentBrand->name,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $assortmentBrandOld = factory(AssortmentBrand::class)->create();
        $assortmentBrandNew = factory(AssortmentBrand::class)->make();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/assortment-brands/{$assortmentBrandOld->uuid}", $assortmentBrandNew->only([
            'name',
        ]));

        $data = [
            'uuid' => $assortmentBrandOld->uuid,
            'name' => $assortmentBrandNew->name,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('assortment_brands', $data);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $assortmentBrand = factory(AssortmentBrand::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/assortment-brands/{$assortmentBrand->uuid}");

        $json->assertSuccessful();
    }

    /**
     * @test
     */
    public function destroyNonEmpty()
    {
        $assortmentBrand = factory(AssortmentBrand::class)->create();

        factory(Assortment::class)->create([
            'assortment_brand_uuid' => $assortmentBrand->uuid,
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/assortment-brands/{$assortmentBrand->uuid}");

        $json->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
