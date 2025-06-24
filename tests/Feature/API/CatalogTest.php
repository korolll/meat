<?php

namespace Tests\Feature\API;

use App\Jobs\ExportCatalogsTo1C;
use App\Models\Assortment;
use App\Models\Catalog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class CatalogTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;


    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.1c.catalog_exporter.uri', 'test_uri');

    }

    /**
     * @test
     */
    public function index()
    {
        $catalog = factory(Catalog::class)->create();

        $self = factory(User::class)->state('store')->create();
        $json = $this->be($self)->getJson('/api/catalogs?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $catalog->uuid,
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        /** @var Catalog $catalog */
        $catalog = factory(Catalog::class)
            ->state('has-image')
            ->create();

        $self = factory(User::class)->state('admin')->create();
        Queue::fake();
        $json = $this->be($self)->postJson('/api/catalogs', $catalog->only([
            'name',
            'image_uuid',
        ]));
        Queue::assertPushed(ExportCatalogsTo1C::class, 1);

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
    public function storeWithLoopingParent()
    {
        /** @var Catalog $catalog1 */
        $catalog1 = factory(Catalog::class)->create();
        /** @var Catalog $catalog2 */
        $catalog2 = factory(Catalog::class)->create([
            'catalog_uuid' => $catalog1->uuid
        ]);
        /** @var Catalog $catalog3 */
        $catalog3 = factory(Catalog::class)->create([
            'catalog_uuid' => $catalog2->uuid
        ]);

        $catalog1->catalog_uuid = $catalog3->uuid;

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/catalogs/{$catalog1->uuid}", $catalog1->only([
            'name',
            'catalog_uuid'
        ]));
        $json
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('catalog_uuid');
    }

    /**
     * @test
     */
    public function storeInCatalogWithAssortments()
    {
        /** @var Catalog $parent */
        $parent = factory(Catalog::class)->state('has-assortment')->create();
        /** @var Catalog $catalog */
        $catalog = factory(Catalog::class)->make([
            'catalog_uuid' => $parent->uuid
        ]);
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/catalogs', $catalog->only([
            'name',
            'catalog_uuid',
        ]));
        $json
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('catalog_uuid');
    }

    /**
     * @test
     */
    public function show()
    {
        $catalog = factory(Catalog::class)->create();

        $self = factory(User::class)->state('admin')->create();
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
        $catalogOld = factory(Catalog::class)->create();
        $catalogNew = factory(Catalog::class)->make();

        $self = factory(User::class)->state('admin')->create();
        Queue::fake();
        $json = $this->be($self)->putJson("/api/catalogs/{$catalogOld->uuid}", $catalogNew->only([
            'name',
        ]));
        Queue::assertPushed(ExportCatalogsTo1C::class, 1);

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
    public function updateWithParentCatalogWithAssortments()
    {
        /** @var Catalog $parent */
        $parent = factory(Catalog::class)->state('has-assortment')->create();
        $catalogOld = factory(Catalog::class)->create();
        $catalogNew = factory(Catalog::class)->make([
            'catalog_uuid' => $parent->uuid
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/catalogs/{$catalogOld->uuid}", $catalogNew->only([
            'name', 'catalog_uuid'
        ]));
        $json
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('catalog_uuid');
    }

    /**
     * @test
     */
    public function destroy()
    {
        $catalog = factory(Catalog::class)->create();

        $self = factory(User::class)->state('admin')->create();
        Queue::fake();
        $json = $this->be($self)->deleteJson("/api/catalogs/{$catalog->uuid}");
        Queue::assertPushed(ExportCatalogsTo1C::class, 1);
        $json->assertSuccessful();
    }

    /**
     * @test
     */
    public function destroyNonEmpty()
    {
        $assortment = factory(Assortment::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/catalogs/{$assortment->catalog_uuid}");

        $json->assertStatus(Response::HTTP_BAD_REQUEST)->assertJson([
            'data' => [
                [
                    'uuid' => $assortment->uuid,
                ],
            ],
        ]);
    }
}
