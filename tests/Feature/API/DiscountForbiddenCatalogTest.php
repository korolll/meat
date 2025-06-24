<?php

namespace Tests\Feature\API;

use App\Jobs\ExportCatalogsTo1C;
use App\Models\Assortment;
use App\Models\Catalog;
use App\Models\DiscountForbiddenCatalog;
use App\Models\User;
use App\Services\Models\Assortment\BannedAssortmentChecker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class DiscountForbiddenCatalogTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function index()
    {
        /** @var DiscountForbiddenCatalog $catalog */
        $catalog = DiscountForbiddenCatalog::factory()->createOne();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/discount-forbidden-catalogs?per_page=1000');

        $json->assertSuccessful()->assertJson([
            'data' => [[
                'uuid' => $catalog->uuid,
                'catalog' => [
                    'uuid' => $catalog->catalog_uuid
                ]
            ]]
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $data = [
            'catalog_uuid' => $assortment->catalog_uuid
        ];
        $json = $this->be($self)->postJson('/api/discount-forbidden-catalogs', $data);

        $checker = new BannedAssortmentChecker();
        $result = $checker->checkCollection([$assortment]);
        $this->assertTrue($result[$assortment->uuid]);

        $json->assertSuccessful()->assertJson([
            'data' => $data
        ]);

        $this->assertDatabaseHas((new DiscountForbiddenCatalog())->getTable(), $data);
    }

    /**
     * @test
     */
    public function storeBulk()
    {
        /** @var DiscountForbiddenCatalog $catalog1 */
        $catalog1 = DiscountForbiddenCatalog::factory()->makeOne();
        /** @var DiscountForbiddenCatalog $catalog2 */
        $catalog2 = DiscountForbiddenCatalog::factory()->makeOne();

        $self = factory(User::class)->state('admin')->create();
        $data = [
            'catalog_uuids' => [
                $catalog1->catalog_uuid,
                $catalog2->catalog_uuid,
            ]
        ];
        $json = $this->be($self)->postJson('/api/discount-forbidden-catalogs/store-bulk', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                ['catalog_uuid' => $catalog1->catalog_uuid],
                ['catalog_uuid' => $catalog2->catalog_uuid],
            ]
        ]);

        $this->assertDatabaseHas($catalog1->getTable(), [
            'catalog_uuid' => $catalog1->catalog_uuid
        ]);
        $this->assertDatabaseHas($catalog2->getTable(), [
            'catalog_uuid' => $catalog2->catalog_uuid
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        /** @var DiscountForbiddenCatalog $catalog */
        $catalog = DiscountForbiddenCatalog::factory()->createOne();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/discount-forbidden-catalogs/{$catalog->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $catalog->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function destroy()
    {
        /** @var DiscountForbiddenCatalog $catalog */
        $catalog = DiscountForbiddenCatalog::factory()->createOne();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/discount-forbidden-catalogs/{$catalog->uuid}");
        $json->assertSuccessful();
        $this->assertDatabaseMissing($catalog->getTable(), ['catalog_uuid' => $catalog->catalog_uuid]);
    }
}
