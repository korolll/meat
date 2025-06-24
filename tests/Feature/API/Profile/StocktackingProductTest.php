<?php

namespace Tests\Feature\API\Profile;

use App\Models\AssortmentProperty;
use App\Models\Product;
use App\Models\Stocktaking;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class StocktackingProductTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        /**
         * @var $stocktaking Stocktaking
         * @var $product Product
         * @var $self User
         */
        $self = factory(User::class)->state('store')->create();
        $stocktaking = factory(Stocktaking::class)->create([
            'user_uuid' => $self->uuid,
        ]);
        for ($i = 1; $i <= 3; $i++) {
            $product = factory(Product::class)->create([
                'user_uuid' => $self->uuid,
            ]);

            $stocktaking->products()->attach($product, [
                'quantity_old' => $product->quantity,
                'quantity_new' => 0,
            ]);
        }

        $self = $stocktaking->user;
        $json = $this->be($self)->getJson("/api/profile/stocktakings/{$stocktaking->uuid}/products");
        $json->assertSuccessful()->assertJsonFragment([
            'product_uuid' => $product->uuid,
            'barcodes' => $product->assortment->barcodes->pluck('barcode')->toArray()
        ]);
    }

    /**
     * @test
     */
    public function indexFilterByCatalog()
    {
        /**
         * @var $stocktaking Stocktaking
         * @var $product Product
         * @var $self User
         */
        $self = factory(User::class)->state('store')->create();
        $stocktaking = factory(Stocktaking::class)->create([
            'user_uuid' => $self->uuid,
        ]);
        for ($i = 1; $i <= 3; $i++) {
            $product = factory(Product::class)->create([
                'user_uuid' => $self->uuid,
            ]);

            $stocktaking->products()->attach($product, [
                'quantity_old' => $product->quantity,
                'quantity_new' => 0,
            ]);
        }

        $assortmentCatalogName = $product->assortment->catalog->name;
        $data = [
            'where' => [
                ['assortment_catalog_name', '=', $assortmentCatalogName]
            ]
        ];
        // Протестим еще и фильтрацию тегов
        $json = $this->be($self)->json('get', "/api/profile/stocktakings/{$stocktaking->uuid}/products", $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'product_uuid' => $product->uuid,
                    'assortment_catalog_name' => $assortmentCatalogName
                ],
            ],
        ]);
    }


    /**
     * @test
     */
    public function indexFilterByBarcode()
    {
        /**
         * @var $stocktaking Stocktaking
         * @var $product Product
         * @var $self User
         */
        $self = factory(User::class)->state('store')->create();
        $stocktaking = factory(Stocktaking::class)->create([
            'user_uuid' => $self->uuid,
        ]);
        for ($i = 1; $i <= 3; $i++) {
            $product = factory(Product::class)->create([
                'user_uuid' => $self->uuid,
            ]);

            $stocktaking->products()->attach($product, [
                'quantity_old' => $product->quantity,
                'quantity_new' => 0,
            ]);
        }

        $barcodes = $product->assortment->barcodes->pluck('barcode')->toArray();

        $data = [
            'where' => [
                ['barcodes', 'in', $barcodes]
            ]
        ];
        // Протестим еще и фильтрацию тегов
        $json = $this->be($self)->json('get', "/api/profile/stocktakings/{$stocktaking->uuid}/products", $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'product_uuid' => $product->uuid,
                    'barcodes' => $barcodes
                ],
            ],
        ]);
    }


    /**
     * @test
     */
    public function indexFilterByAssortmentProperty()
    {
        /**
         * @var $stocktaking Stocktaking
         * @var $product Product
         * @var $self User
         */
        $self = factory(User::class)->state('store')->create();
        $stocktaking = factory(Stocktaking::class)->create([
            'user_uuid' => $self->uuid,
        ]);
        for ($i = 1; $i <= 3; $i++) {
            $product = factory(Product::class)->create([
                'user_uuid' => $self->uuid,
            ]);

            $stocktaking->products()->attach($product, [
                'quantity_old' => $product->quantity,
                'quantity_new' => 0,
            ]);
        }

        $assortment = $product->assortment;

        $value1 = 'qweqweqweqweqw';
        $value2 = 'asdasdasdasdas';

        /**
         * @var $assortmentProperty1 AssortmentProperty
         * @var $assortmentProperty2 AssortmentProperty
         */
        $assortmentProperty1 = factory(AssortmentProperty::class)->make();
        $assortmentProperty2 = factory(AssortmentProperty::class)->make();
        $assortment->assortmentProperties()->save($assortmentProperty1, ['value' => $value1]);
        $assortment->assortmentProperties()->save($assortmentProperty2, ['value' => $value2]);

        $self->assortmentMatrix()->attach($assortment);

        $data = [
            '*RequestFilters' => [
                'assortment_properties' => [
                    'uuid' => $assortmentProperty1->uuid,
                    'value' => $value1,
                    'operator' => '=',
                ]
            ]
        ];
        // Протестим еще и фильтрацию тегов
        $json = $this->be($self)->json('get', "/api/profile/stocktakings/{$stocktaking->uuid}/products", $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'product_uuid' => $product->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        /** @var Stocktaking $stocktaking */
        $stocktaking = factory(Stocktaking::class)->create();
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'user_uuid' => $stocktaking->user_uuid,
        ]);

        $stocktaking->products()->attach($product, [
            'quantity_old' => $product->quantity,
            'quantity_new' => 0,
        ]);

        $self = $stocktaking->user;
        $json = $this->be($self)->putJson("/api/profile/stocktakings/{$stocktaking->uuid}/products/{$product->uuid}", [
            'quantity_new' => 31337,
        ]);

        $json->assertSuccessful();
        $this->assertDatabaseHas('product_stocktaking', [
            'stocktaking_uuid' => $stocktaking->uuid,
            'product_uuid' => $product->uuid,
            'quantity_new' => 31337,
        ]);
    }

    /**
     * @test
     */
    public function updateBatch()
    {
        /** @var Stocktaking $stocktaking */
        $stocktaking = factory(Stocktaking::class)->create();
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'user_uuid' => $stocktaking->user_uuid,
        ]);
        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $stocktaking->user_uuid,
        ]);

        $stocktaking->products()->attach($product, [
            'quantity_old' => $product->quantity,
            'quantity_new' => 0,
        ]);
        $stocktaking->products()->attach($product2, [
            'quantity_old' => $product2->quantity,
            'quantity_new' => 0,
        ]);

        $quantityNew1 = $this->faker->numberBetween(100, 10000);
        $quantityNew2 = $this->faker->numberBetween(100, 10000);
        $self = $stocktaking->user;
        $json = $this->be($self)->postJson("/api/profile/stocktakings/{$stocktaking->uuid}/products/batch-update", [
            'products' => [
                ['product_uuid' => $product->uuid, 'quantity_new' => $quantityNew1],
                ['product_uuid' => $product2->uuid, 'quantity_new' => $quantityNew2]
            ]
        ]);

        $json->assertSuccessful();
        $this->assertDatabaseHas('product_stocktaking', [
            'stocktaking_uuid' => $stocktaking->uuid,
            'product_uuid' => $product->uuid,
            'quantity_new' => $quantityNew1,
        ]);
        $this->assertDatabaseHas('product_stocktaking', [
            'stocktaking_uuid' => $stocktaking->uuid,
            'product_uuid' => $product2->uuid,
            'quantity_new' => $quantityNew2,
        ]);
    }
}
