<?php

namespace Tests\Feature\API\User;

use App\Models\Assortment;
use App\Models\AssortmentProperty;
use App\Models\Catalog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class CatalogAssortmentPropertyTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $catalog = factory(Catalog::class)->create();
        $assortmentProperty = factory(AssortmentProperty::class)->create();

        $catalog->assortmentProperties()->attach($assortmentProperty);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/catalogs/{$catalog->uuid}/assortment-properties");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $assortmentProperty->uuid,
                    'name' => $assortmentProperty->name,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $assortmentProperty = factory(AssortmentProperty::class)->create();

        $catalog1 = factory(Catalog::class)->create();
        $catalog2 = factory(Catalog::class)->create([
            'catalog_uuid' => $catalog1->uuid,
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson("/api/catalogs/{$catalog1->uuid}/assortment-properties", [
            'assortment_property_uuid' => $assortmentProperty->uuid,
        ]);

        $json->assertSuccessful();

        $this->assertDatabaseHas('assortment_property_catalog', [
            'catalog_uuid' => $catalog1->uuid,
            'assortment_property_uuid' => $assortmentProperty->uuid,
        ]);

        $this->assertDatabaseHas('assortment_property_catalog', [
            'catalog_uuid' => $catalog2->uuid,
            'assortment_property_uuid' => $assortmentProperty->uuid,
        ]);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $assortmentProperty = factory(AssortmentProperty::class)->create();

        $catalog1 = factory(Catalog::class)->create();
        $catalog2 = factory(Catalog::class)->create([
            'catalog_uuid' => $catalog1->uuid,
        ]);

        $catalog1->assortmentProperties()->attach($assortmentProperty);
        $catalog2->assortmentProperties()->attach($assortmentProperty);

        factory(Assortment::class, 2)->create([
            'catalog_uuid' => $catalog2->uuid,
        ])->each(function (Assortment $assortment) use ($assortmentProperty) {
            $assortment->assortmentProperties()->attach($assortmentProperty, [
                'value' => 'hello kitty',
            ]);
        });

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/catalogs/{$catalog1->uuid}/assortment-properties/{$assortmentProperty->uuid}");

        $json->assertSuccessful();

        $this->assertDatabaseMissing('assortment_property_catalog', [
            'assortment_property_uuid' => $assortmentProperty->uuid,
        ]);

        $this->assertDatabaseMissing('assortment_assortment_property', [
            'assortment_property_uuid' => $assortmentProperty->uuid,
        ]);
    }
}
