<?php

namespace Tests\Feature\API\Profile;

use App\Models\Catalog;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class CatalogTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $catalog = factory(Catalog::class)->state('private')->create();

        $self = $catalog->user;
        $json = $this->be($self)->getJson('/api/profile/catalogs');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $catalog->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        /** @var Catalog $catalog */
        $catalog = factory(Catalog::class)
            ->states([
                'private',
                'has-image'
            ])
            ->create();

        $self = $catalog->user;
        $json = $this->be($self)->postJson('/api/profile/catalogs', $catalog->only([
            'name',
            'image_uuid',
        ]));

        $data = [
            'uuid' => $json->json('data.uuid'),
            'name' => $catalog->name,
            'image' => [
                'uuid' => $catalog->image->uuid,
                'thumbnails' => [],
                'path' => Storage::url($catalog->image->path),
            ]
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        unset($data['image']);
        $data['image_uuid'] = $catalog->image->uuid;
        $this->assertDatabaseHas('catalogs', $data);
    }

    /**
     * @test
     */
    public function show()
    {
        $catalog = factory(Catalog::class)->state('private')->create();

        $self = $catalog->user;
        $json = $this->be($self)->getJson("/api/catalogs/{$catalog->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $catalog->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $catalogOld = factory(Catalog::class)->state('private')->create();
        $catalogNew = factory(Catalog::class)->state('private')->make();

        $self = $catalogOld->user;
        $json = $this->be($self)->putJson("/api/catalogs/{$catalogOld->uuid}", $catalogNew->only([
            'name',
        ]));

        $data = [
            'uuid' => $catalogOld->uuid,
            'name' => $catalogNew->name,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('catalogs', $data);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $catalog = factory(Catalog::class)->state('private')->create();

        $self = $catalog->user;
        $json = $this->be($self)->deleteJson("/api/catalogs/{$catalog->uuid}");

        $json->assertSuccessful();
    }

    /**
     * @test
     */
    public function destroyNonEmpty()
    {
        $product = factory(Product::class)->create();

        $self = $product->user;
        $json = $this->be($self)->deleteJson("/api/catalogs/{$product->catalog_uuid}");

        $json->assertStatus(Response::HTTP_BAD_REQUEST)->assertJson([
            'data' => [
                [
                    'uuid' => $product->assortment_uuid,
                ],
            ],
        ]);
    }
}
