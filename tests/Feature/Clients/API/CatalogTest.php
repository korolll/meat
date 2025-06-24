<?php

namespace Tests\Feature\Clients\API;

use App\Jobs\UpdateUsersCachedProductsCountInCatalogs;
use App\Models\Assortment;
use App\Models\AssortmentProperty;
use App\Models\Catalog;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class CatalogTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testCachedMap()
    {
        /** @var AssortmentProperty $property1 */
        $property1 = factory(AssortmentProperty::class)->create();
        /** @var AssortmentProperty $property2 */
        $property2 = factory(AssortmentProperty::class)->create();
        /** @var AssortmentProperty $property3 */
        $property3 = factory(AssortmentProperty::class)->create();

        /** @var Tag $tag1 */
        $tag1 = factory(Tag::class)->create();
        /** @var Tag $tag2 */
        $tag2 = factory(Tag::class)->create();
        /** @var Tag $tag3 */
        $tag3 = factory(Tag::class)->create();

        // Catalogs
        /** @var Catalog[] $catalogs */
        $catalogs = [];
        $catalogs[0] = factory(Catalog::class)->create();
        $catalogs[1] = factory(Catalog::class)->create([
            'catalog_uuid' => $catalogs[0]->uuid
        ]);
        $catalogs[2] = factory(Catalog::class)->create([
            'catalog_uuid' => $catalogs[0]->uuid
        ]);
        $catalogs[3] = factory(Catalog::class)->create([
            'catalog_uuid' => $catalogs[2]->uuid
        ]);
        $catalogs[4] = factory(Catalog::class)->create([
            'catalog_uuid' => $catalogs[2]->uuid
        ]);
        $catalogs[5] = factory(Catalog::class)->create();
        $catalogs[6] = factory(Catalog::class)->create();

        // User one
        /** @var \App\Models\User $user */
        $user = factory(\App\Models\User::class)->state('store')->create();

        // Pr 1
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'catalog_uuid' => $catalogs[1]->uuid
        ]);
        factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'assortment_uuid' => $assortment->uuid,
            'catalog_uuid' => $catalogs[1]->uuid,
        ]);

        // Pr 2
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'catalog_uuid' => $catalogs[2]->uuid
        ]);
        $assortment->assortmentProperties()->sync([$property1->uuid => ['value' => 1]]);
        $assortment->tags()->sync([$tag1->uuid]);
        factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'assortment_uuid' => $assortment->uuid,
            'catalog_uuid' => $catalogs[2]->uuid,
        ]);

        // Pr 3
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'catalog_uuid' => $catalogs[3]->uuid
        ]);
        $assortment->assortmentProperties()->sync([$property1->uuid => ['value' => 1]]);
        $assortment->tags()->sync([$tag1->uuid]);
        factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'assortment_uuid' => $assortment->uuid,
            'catalog_uuid' => $catalogs[3]->uuid,
        ]);

        // Pr 4
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'catalog_uuid' => $catalogs[4]->uuid
        ]);
        $assortment->assortmentProperties()->sync([$property2->uuid => ['value' => 2]]);
        $assortment->tags()->sync([$tag2->uuid]);
        factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'assortment_uuid' => $assortment->uuid,
            'catalog_uuid' => $catalogs[4]->uuid,
        ]);

        // Pr 5
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'catalog_uuid' => $catalogs[4]->uuid
        ]);
        $assortment->assortmentProperties()->sync([$property3->uuid => ['value' => 3]]);
        $assortment->tags()->sync([$tag3->uuid]);
        factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'assortment_uuid' => $assortment->uuid,
            'catalog_uuid' => $catalogs[4]->uuid,
        ]);

        UpdateUsersCachedProductsCountInCatalogs::dispatchNow();
        $this->assertDatabaseHas('user_catalog_product_counts', [
            'user_uuid' => $user->uuid,
            'catalog_uuid' => $catalogs[0]->uuid,
            'product_count' => 5,
            'properties' => json_encode([$property1->uuid, $property2->uuid, $property3->uuid]),
            'tags' => json_encode([$tag1->uuid, $tag2->uuid, $tag3->uuid]),
        ]);
        $this->assertDatabaseHas('user_catalog_product_counts', [
            'user_uuid' => $user->uuid,
            'catalog_uuid' => $catalogs[1]->uuid,
            'product_count' => 1,
            'properties' => null,
            'tags' => null,
        ]);
        $this->assertDatabaseHas('user_catalog_product_counts', [
            'user_uuid' => $user->uuid,
            'catalog_uuid' => $catalogs[2]->uuid,
            'product_count' => 4,
            'properties' => json_encode([$property1->uuid, $property2->uuid, $property3->uuid]),
            'tags' => json_encode([$tag1->uuid, $tag2->uuid, $tag3->uuid]),
        ]);
        $this->assertDatabaseHas('user_catalog_product_counts', [
            'user_uuid' => $user->uuid,
            'catalog_uuid' => $catalogs[3]->uuid,
            'product_count' => 1,
            'properties' => json_encode([$property1->uuid]),
            'tags' => json_encode([$tag1->uuid])
        ]);
        $this->assertDatabaseHas('user_catalog_product_counts', [
            'user_uuid' => $user->uuid,
            'catalog_uuid' => $catalogs[4]->uuid,
            'product_count' => 2,
            'properties' => json_encode([$property2->uuid, $property3->uuid]),
            'tags' => json_encode([$tag2->uuid, $tag3->uuid])
        ]);
    }

    /**
     * @test
     */
    public function index()
    {
        /** @var Catalog $catalog */
        $catalog = factory(Catalog::class)->state('has-image')->create();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'catalog_uuid' => $catalog->uuid,
            'assortment_verify_status_id' => 'approved'
        ]);

        /** @var \App\Models\User $user */
        $user = factory(User::class)->state('store')->create();

        /** @var AssortmentProperty $property */
        $property = factory(AssortmentProperty::class)->create();
        $assortment->assortmentProperties()->sync([$property->uuid => ['value' => 1]]);
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();
        $assortment->tags()->sync([$tag->uuid]);
        factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'catalog_uuid' => $catalog->uuid,
            'user_uuid' => $user->uuid
        ]);

        $filters = [
            'store_uuid' => $user->uuid,
            'where' => [
                ['assortments_count_in_store', '=', 1]
            ]
        ];

        UpdateUsersCachedProductsCountInCatalogs::dispatchNow();
        $catalog->refresh();
        $json = $this->getJson('/clients/api/catalogs?' . http_build_query($filters));
        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $catalog->uuid,
            'level' => $catalog->level,
            'assortments_count' => $catalog->assortments_count,
            'assortments_count_in_store' => 1,
            'assortments_properties_in_store' => [$property->uuid],
            'assortments_tags_in_store' => [$tag->name],
            'is_final_level' => true,
            'image' => [
                'uuid' => $catalog->image->uuid,
                'thumbnails' => [],
                'path' => Storage::url($catalog->image->path),
            ]
        ]);
    }
}
