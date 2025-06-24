<?php

namespace Tests\Feature\API\Profile;

use App\Jobs\ExportPriceListToAtol;
use App\Jobs\ExportProductTo1C;
use App\Jobs\UpdateCatalogProductCountJob;
use App\Models\AssortmentBarcode;
use App\Models\AssortmentProperty;
use App\Models\Catalog;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\PriceList;
use App\Models\PriceListStatus;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class ProductTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $product = factory(Product::class)->create();

        $self = $product->user;
        $json = $this->be($self)->getJson('/api/profile/products');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $product->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function indexWithFilterByTags()
    {
//        $self = factory(User::class)->state('store')->create();

        /** @var Product $product */
        $product = factory(Product::class)->create();
        $assortment = $product->assortment;

        $tagOne = factory(Tag::class)->create();
        $tagTwo = factory(Tag::class)->create();
        $assortment->tags()->attach($tagOne);
        $assortment->tags()->attach($tagTwo);

        $self = $product->user;
        $self->assortmentMatrix()->attach($assortment);

        $data = ['where' => [['assortment_tags', 'in', $tagOne->name]]];
        $json = $this->be($self)->json('get', '/api/profile/products', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $product->uuid,
                    'assortment_tags' => [$tagOne->name, $tagTwo->name]
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function indexWithFilterByCatalog()
    {
        $self = factory(User::class)->state('distribution-center')->create();

        $catalog = factory(Catalog::class)->create([
            'user_uuid' => $self->uuid
        ]);
        /** @var Product $product1 */
        /** @var Product $product2 */
        /** @var Product $product3 */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $self->uuid
        ]);
        $product2 = factory(Product::class)->create([
            'user_uuid' => $self->uuid
        ]);
        $product3 = factory(Product::class)->create([
            'user_uuid' => $self->uuid
        ]);

        $assortment = $product1->assortment;
        $assortment->catalog()->associate($catalog)->save();

        $self->assortmentMatrix()->attach($assortment);

        $data = ['where' => [['assortment_catalog_name', 'in', $catalog->name]]];
        $json = $this->be($self)->json('get', '/api/profile/products', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $product1->uuid,
                    'assortment_catalog_name' => $catalog->name
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function indexWithFilterByBarcodes()
    {
        $self = factory(User::class)->state('distribution-center')->create();

        /** @var Product $product1 */
        $product1 = factory(Product::class)->create(['user_uuid' => $self->uuid]);
        /** @var Product $product2 */
        factory(Product::class)->create(['user_uuid' => $self->uuid]);
        /** @var Product $product3 */
        factory(Product::class)->create(['user_uuid' => $self->uuid]);

        $assortment = $product1->assortment;
        /**
         * @var $assortmentBarcode1 AssortmentBarcode
         * @var $assortmentBarcode2 AssortmentBarcode
         */
        $assortmentBarcode1 = factory(AssortmentBarcode::class)->make();
        $assortmentBarcode2 = factory(AssortmentBarcode::class)->make();
        $assortment->barcodes()->saveMany([$assortmentBarcode1, $assortmentBarcode2]);
        $assortment->save();

        $self->assortmentMatrix()->attach($assortment);
        $barcodes = $product1->assortment->barcodes->pluck('barcode')->toArray();

        $data = [
            'where' => [
                ['barcodes', 'in', $barcodes]
            ]
        ];
        $json = $this->be($self)->json('get', '/api/profile/products', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $product1->uuid,
                    'barcodes' => $barcodes
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function indexWithFilterByAssortmentProperty()
    {
        $self = factory(User::class)->state('distribution-center')->create();

        /** @var Product $product1 */
        /** @var Product $product2 */
        /** @var Product $product3 */
        $product1 = factory(Product::class)->create(['user_uuid' => $self->uuid]);
        factory(Product::class)->create(['user_uuid' => $self->uuid]);
        factory(Product::class)->create(['user_uuid' => $self->uuid]);

        $assortment = $product1->assortment;
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
                'assortment_properties' => [[
                    'uuid' => $assortmentProperty1->uuid,
                    'value' => $value1,
                    'operator' => '=',
                ]]
            ]
        ];
        $json = $this->be($self)->json('get', '/api/profile/products', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                ['uuid' => $product1->uuid],
            ],
        ]);
    }


    /**
     * @test
     */
    public function create()
    {
        $product = factory(Product::class)->make();

        $user = $product->user;

        $file = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_PRODUCT_FILE,
        ]);
        $publicName = 'some public name';

        Queue::fake();
        Config::set('services.1c.product_exporter.uri', '123');
        Config::set('services.1c.users_allowed_to_export', $product->user_uuid);

        $json = $this->be($user)->postJson('/api/profile/products', array_merge($product->only([
            'catalog_uuid',
            'assortment_uuid',
            'quantum',
            'min_quantum_in_order',
            'min_delivery_time',
            'price_recommended',
            'delivery_weekdays',
            'volume',
        ]), [
            'files' => [
                ['uuid' => $file->uuid, 'public_name' => $publicName]
            ]
        ]));

        $data = [
            'catalog_uuid' => $product->catalog_uuid,
            'assortment_uuid' => $product->assortment_uuid,
            'quantum' => $product->quantum,
            'min_quantum_in_order' => $product->min_quantum_in_order,
            'min_delivery_time' => $product->min_delivery_time,
            'price_recommended' => $product->price_recommended,
            'delivery_weekdays' => $product->delivery_weekdays,
            'volume' => $product->volume,
            'files' => [
                [
                    'uuid' => $file->uuid,
                    'path' => Storage::url($file->path),
                    'public_name' => $publicName
                ]
            ]
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('products', Arr::except($data, ['delivery_weekdays', 'files']));
        Queue::assertPushed(ExportProductTo1C::class);
        Queue::assertPushed(UpdateCatalogProductCountJob::class);

        $this->assertDatabaseHas('assortment_matrices', [
            'user_uuid' => $product->user_uuid,
            'assortment_uuid' => $product->assortment_uuid
        ]);
    }

    /**
     * @test
     */
    public function createWithCatalogCountCheck()
    {
        $product = factory(Product::class)->make();

        $user = $product->user;

        $file = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_PRODUCT_FILE,
        ]);
        $publicName = 'some public name';

        $this->be($user)->postJson('/api/profile/products', array_merge($product->only([
            'catalog_uuid',
            'assortment_uuid',
            'quantum',
            'min_quantum_in_order',
            'min_delivery_time',
            'price_recommended',
            'delivery_weekdays',
            'volume',
        ]), [
            'files' => [
                ['uuid' => $file->uuid, 'public_name' => $publicName]
            ]
        ]));

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $product->catalog_uuid,
            'products_count' => 1,
        ]);
    }


    /**
     * @test
     */
    public function show()
    {
        $product = factory(Product::class)->state('has-file')->create();

        $self = $product->user;
        $json = $this->be($self)->getJson(sprintf('/api/profile/products/%s', $product->uuid));

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $product->uuid,
                'price_recommended' => $product->price_recommended,
                'delivery_weekdays' => $product->delivery_weekdays,
                'volume' => $product->volume,
                'files' => $product->files->map(function ($file) {
                    return [
                        'uuid' => $file->uuid,
                        'path' => Storage::url($file->path),
                        'public_name' => $file->pivot->public_name
                    ];
                })->all(),
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $productOld = factory(Product::class)->create();
        $productNew = factory(Product::class)->make([
            'user_uuid' => $productOld->user_uuid,
        ]);

        $user = $productOld->user;

        $file = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_PRODUCT_FILE,
        ]);
        $publicName = 'some public name';

        Queue::fake();
        Config::set('services.1c.product_exporter.uri', '123');
        Config::set('services.1c.users_allowed_to_export', $productOld->user_uuid);
        $json = $this->be($user)->putJson(sprintf('/api/profile/products/%s', $productOld->uuid), array_merge($productNew->only([
            'quantum',
            'min_quantum_in_order',
            'min_delivery_time',
            'catalog_uuid',
            'price_recommended',
            'delivery_weekdays',
            'volume',
        ]), [
            'files' => [
                ['uuid' => $file->uuid, 'public_name' => $publicName],
            ]
        ]));

        $data = [
            'uuid' => $productOld->uuid,
            'quantum' => $productNew->quantum,
            'min_quantum_in_order' => $productNew->min_quantum_in_order,
            'min_delivery_time' => $productNew->min_delivery_time,
            'catalog_uuid' => $productNew->catalog_uuid,
            'price_recommended' => $productNew->price_recommended,
            'delivery_weekdays' => $productNew->delivery_weekdays,
            'volume' => $productNew->volume,
            'files' => [
                [
                    'uuid' => $file->uuid,
                    'path' => Storage::url($file->path),
                    'public_name' => $publicName
                ]
            ]
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('products', Arr::except($data, ['delivery_weekdays', 'files']));
        Queue::assertPushed(ExportProductTo1C::class);
        Queue::assertPushed(UpdateCatalogProductCountJob::class);
    }

    /**
     * @test
     */
    public function updateWithCatalogCountCheck()
    {
        $productOld = factory(Product::class)->create();
        $productNew = factory(Product::class)->make([
            'user_uuid' => $productOld->user_uuid,
        ]);

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $productOld->catalog_uuid,
            'products_count' => 1,
        ]);

        $user = $productOld->user;

        $file = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_PRODUCT_FILE,
        ]);
        $publicName = 'some public name';

        $this->be($user)->putJson(sprintf('/api/profile/products/%s', $productOld->uuid), array_merge($productNew->only([
            'quantum',
            'min_quantum_in_order',
            'min_delivery_time',
            'catalog_uuid',
            'price_recommended',
            'delivery_weekdays',
            'volume',
        ]), [
            'files' => [
                ['uuid' => $file->uuid, 'public_name' => $publicName],
            ]
        ]));

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $productOld->catalog_uuid,
            'products_count' => 0,
        ]);

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $productNew->catalog_uuid,
            'products_count' => 1,
        ]);
    }

    /**
     * @param array $deliveryWeekdays
     *
     * @test
     * @testWith [[0, 1, 2, 3, 4, 5, 6]]
     *           [[]]
     */
    public function setDeliveryWeekdays(array $deliveryWeekdays)
    {
        $self = factory(User::class)->state('supplier')->create();

        /** @var \Illuminate\Support\Collection|Product[] $products */
        $products = factory(Product::class, 2)->create([
            'user_uuid' => $self->uuid,
            'delivery_weekdays' => [],
        ]);

        $json = $this->be($self)->putJson('/api/profile/products/set-delivery-weekdays', [
            'products' => $products->map->only('uuid')->all(),
            'delivery_weekdays' => $deliveryWeekdays,
        ]);

        $json->assertSuccessful();

        foreach ($products as $product) {
            $this->assertEquals($deliveryWeekdays, $product->refresh()->delivery_weekdays);
        }
    }

    /**
     * @test
     */
    public function setIsActive()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'is_active' => true
        ]);
        /** @var PriceList $price_list */
        $price_list = factory(PriceList::class)->create([
            'price_list_status_id' => PriceListStatus::CURRENT,
            'user_uuid' => $product->user_uuid
        ]);
        /** @var PriceList $price_list_private */
        $price_list_private = factory(PriceList::class)->state('private')->create([
            'price_list_status_id' => PriceListStatus::CURRENT,
            'user_uuid' => $product->user_uuid
        ]);
        /** @var PriceList $price_list_private */
        $price_list_not_current = factory(PriceList::class)->create([
            'price_list_status_id' => PriceListStatus::FUTURE,
            'user_uuid' => $product->user_uuid
        ]);

        $price_list->products()->attach($product);
        $price_list_private->products()->attach($product);
        $price_list_not_current->products()->attach($product);

        Queue::fake();
        Config::set('services.atol.export.price_list.uri', '123');
        Config::set('services.1c.product_exporter.uri', '123');
        Config::set('services.1c.users_allowed_to_export', $product->user_uuid);

        $json = $this->be($product->user)->putJson(sprintf('/api/profile/products/%s/set-is-active', $product->uuid), [
            'is_active' => false
        ]);

        $json->assertSuccessful();

        $this->assertDatabaseHas('products', [
            'uuid' => $product->uuid,
            'is_active' => false
        ]);

        Queue::assertPushed(ExportProductTo1C::class);
        // должен экспортироваться все текущие прайсы с данным продуктом
        Queue::assertPushed(ExportPriceListToAtol::class, 2);
    }
}
