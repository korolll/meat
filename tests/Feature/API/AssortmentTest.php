<?php

namespace Tests\Feature\API;

use App\Jobs\ExportProductTo1C;
use App\Jobs\UpdateCatalogAssortmentCountJob;
use App\Models\Assortment;
use App\Models\AssortmentBarcode;
use App\Models\AssortmentProperty;
use App\Models\AssortmentUnit;
use App\Models\AssortmentVerifyStatus;
use App\Models\Catalog;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\ProductRequestSupplierStatus;
use App\Models\RatingType;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class AssortmentTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * Публичное имя для изображений ассортимента
     */
    const ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE = 'some public name';

    /**
     * @test
     */
    public function index()
    {
        /**
         * @var $assortment Assortment
         */
        $assortment = factory(Assortment::class)->create();

        /**
         * @var $self User
         */
        $self = factory(User::class)->state('store')->create();
        $self->assortmentMatrix()->attach($assortment);

        $json = $this->be($self)->getJson('/api/assortments?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $assortment->uuid,
            'is_exists_in_assortment_matrix' => true,
            'barcodes' => $assortment->barcodes->pluck('barcode'),
        ]);
    }

    /**
     * @test
     */
    public function indexWithFilterByTag()
    {
        $assortment = factory(Assortment::class)->create();

        $tag1 = factory(Tag::class)->create();
        $tag2 = factory(Tag::class)->create();
        $assortment->tags()->attach($tag1);
        $assortment->tags()->attach($tag2);

        $self = factory(User::class)->state('store')->create();
        $self->assortmentMatrix()->attach($assortment);

        $data = [
            'where' => [
                ['tags', 'in', $tag1->name]
            ]
        ];
        // Протестим еще и фильтрацию тегов
        $json = $this->be($self)->json('get', '/api/assortments', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $assortment->uuid,
                    'tags' => [$tag1->name, $tag2->name],
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function indexWithFilterByBarcode()
    {
        /**
         * @var $assortment1 Assortment
         */
        $assortment1 = factory(Assortment::class)->create();
        $assortment2 = factory(Assortment::class)->create();
        $assortment3 = factory(Assortment::class)->create();

        /**
         * @var $assortmentBarcode1 AssortmentBarcode
         * @var $assortmentBarcode2 AssortmentBarcode
         */
        $assortmentBarcode1 = factory(AssortmentBarcode::class)->make();
        $assortmentBarcode2 = factory(AssortmentBarcode::class)->make();
        $assortment1->barcodes()->saveMany([$assortmentBarcode1, $assortmentBarcode2]);

        $self = factory(User::class)->state('store')->create();
        $self->assortmentMatrix()->attach($assortment1);
        $self->assortmentMatrix()->attach($assortment2);
        $self->assortmentMatrix()->attach($assortment3);

        $data = [
            'where' => [
                ['barcodes', 'in', $assortmentBarcode1->barcode]
            ]
        ];
        // Протестим еще и фильтрацию тегов
        $json = $this->be($self)->json('get', '/api/assortments', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $assortment1->uuid,
                    'barcodes' => $assortment1->barcodes->pluck('barcode')->toArray(),
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function create()
    {
        $catalog = factory(Catalog::class)->state('has-parent')->create();
        /**
         * @var $assortment Assortment
         */
        $assortment = factory(Assortment::class)->make([
            'catalog_uuid' => $catalog->uuid,
            'bonus_percent' => $this->faker->numberBetween(1, 10),
        ]);

        $assortmentProperty = factory(AssortmentProperty::class)->create();
        $catalog->assortmentProperties()->attach($assortmentProperty);

        $user = factory(User::class)->state('distribution-center')->create();
        $image = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
        ]);
        $file = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_FILE,
        ]);

        $tag = factory(Tag::class)->make();
        $assortmentBarcode1 = factory(AssortmentBarcode::class)->make();
        $assortmentBarcode2 = factory(AssortmentBarcode::class)->state('ean8')->make();

        $json = $this->be($user)->postJson('/api/assortments', array_merge($assortment->only([
            'catalog_uuid',
            'name',
            'assortment_unit_id',
            'country_id',
            'okpo_code',
            'weight',
            'volume',
            'manufacturer',
            'ingredients',
            'description',
            'group_barcode',
            'temperature_min',
            'temperature_max',
            'production_standard_id',
            'production_standard_number',
            'is_storable',
            'shelf_life',
            'nds_percent',
            'short_name',
            'assortment_brand_uuid',
            'declaration_end_date',
            'article',
            'bonus_percent',
        ]), [
            'images' => [
                [
                    'uuid' => $image->uuid,
                    'public_name' => static::ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE,
                ],
            ],
            'files' => [
                [
                    'uuid' => $file->uuid,
                    'public_name' => 'file public name',
                ],
            ],
            'properties' => [
                [
                    'uuid' => $assortmentProperty->uuid,
                    'value' => 'hello kitty',
                ],
            ],
            'tags' => [
                $tag->name
            ],
            'barcodes' => [
                $assortmentBarcode1->barcode,
                $assortmentBarcode2->barcode,
            ]
        ]));

        $data = [
            'article' => $assortment->article,
            'declaration_end_date' => $assortment->declaration_end_date,
            'short_name' => $assortment->short_name,
            'images' => [
                [
                    'uuid' => $image->uuid,
                    'path' => Storage::url($image->path),
                    'public_name' => static::ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE,
                ],
            ],
            'properties' => [
                [
                    'uuid' => $assortmentProperty->uuid,
                    'name' => $assortmentProperty->name,
                    'value' => 'hello kitty',
                ],
            ],
            'files' => [
                [
                    'uuid' => $file->uuid,
                    'path' => Storage::url($file->path),
                    'public_name' => 'file public name',
                ],
            ],
            'tags' => [
                $tag->name
            ],
            'barcodes' => [
                $assortmentBarcode1->barcode,
                $assortmentBarcode2->barcode,
            ]
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('assortments', Arr::except($data, ['images', 'properties', 'files', 'tags', 'barcodes']));

        $this->assertDatabaseHas('assortment_assortment_property', [
            'assortment_uuid' => $json->json('data.uuid'),
            'assortment_property_uuid' => $assortmentProperty->uuid,
            'value' => 'hello kitty',
        ]);

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $catalog->uuid,
            'assortments_count' => 1,
        ]);

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $catalog->catalog_uuid,
            'assortments_count' => 1,
        ]);

        $this->assertDatabaseHas('assortment_file', [
            'assortment_uuid' => $json->json('data.uuid'),
            'file_uuid' => $image->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
            'public_name' => self::ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE,
        ]);

        $this->assertDatabaseHas('assortment_file', [
            'assortment_uuid' => $json->json('data.uuid'),
            'file_uuid' => $file->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_FILE,
            'public_name' => 'file public name',
        ]);

        $this->assertDatabaseHas('assortment_barcodes', [
            'assortment_uuid' => $json->json('data.uuid'),
            'barcode' => $assortmentBarcode1->barcode,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('assortment_barcodes', [
            'assortment_uuid' => $json->json('data.uuid'),
            'barcode' => $assortmentBarcode2->barcode,
            'is_active' => true,
        ]);
    }

    /**
     * @test
     */
    public function createWithCatalogNotLastLevel()
    {
        $catalog = factory(Catalog::class)->state('has-children')->create();
        $assortment = factory(Assortment::class)->make([
            'catalog_uuid' => $catalog->uuid,
        ]);
        $user = factory(User::class)->state('distribution-center')->create();

        $json = $this->be($user)->postJson('/api/assortments', $assortment->only([
            'catalog_uuid',
        ]));
        $json
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('catalog_uuid');
    }

    /**
     * @test
     */
    public function testView()
    {
        $assortment = factory(Assortment::class)->create();
        $tag = factory(Tag::class)->create();
        $assortment->tags()->attach($tag->uuid);

        $self = factory(User::class)->state('distribution-center')->create();
        $json = $this->be($self)->getJson(sprintf('/api/assortments/%s', $assortment->uuid));

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $assortment->uuid,
                'short_name' => $assortment->short_name,

                'tags' => [$tag->name]
            ],
        ]);
    }

    /**
     * @param string $userState
     *
     * @test
     * @testWith ["admin"]
     *           ["distribution-center"]
     */
    public function update($userState)
    {
        /** @var Assortment $assortmentOld */
        $assortmentOld = factory(Assortment::class)->create([
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
        ]);

        /** @var AssortmentBarcode $assortmentBarcodeOld */
        $assortmentBarcodeOld = factory(AssortmentBarcode::class)->create([
            'assortment_uuid' => $assortmentOld->uuid
        ]);

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $assortmentOld->catalog_uuid,
            'assortments_count' => 1,
        ]);

        /** @var Assortment $assortmentNew */
        $assortmentNew = factory(Assortment::class)->make([
            'barcodes' => $assortmentOld->barcodes,
            'bonus_percent' => $this->faker->numberBetween(1, 10),
        ]);

        /** @var User $user */
        $user = factory(User::class)->state($userState)->create();
        /** @var File $image */
        $image = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
        ]);
        /** @var File $file */
        $file = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_FILE,
        ]);

        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortmentOld->uuid
        ]);

        /** @var Tag $tag */
        $tag = factory(Tag::class)->make();

        /** @var AssortmentBarcode $assortmentBarcode1 */
        $assortmentBarcode1 = factory(AssortmentBarcode::class)->make();

        Queue::fake();
        Config::set('services.1c.product_exporter.uri', '123');
        Config::set('services.1c.users_allowed_to_export', $product->user_uuid);
        $json = $this->be($user)->putJson("/api/assortments/{$assortmentOld->uuid}", array_merge($assortmentNew->only([
            'catalog_uuid',
            'name',
            'short_name',
            'assortment_unit_id',
            'country_id',
            'okpo_code',
            'weight',
            'volume',
            'manufacturer',
            'ingredients',
            'description',
            'group_barcode',
            'temperature_min',
            'temperature_max',
            'production_standard_id',
            'production_standard_number',
            'is_storable',
            'shelf_life',
            'nds_percent',
            'assortment_brand_uuid',
            'bonus_percent',
        ]), [
            'images' => [
                [
                    'uuid' => $image->uuid,
                    'public_name' => static::ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE,
                ],
            ],
            'files' => [
                [
                    'uuid' => $file->uuid,
                    'public_name' => 'file public name',
                ],
            ],
            'properties' => [
                //
            ],
            'tags' => [
                $tag->name
            ],
            'barcodes' => [
                $assortmentBarcodeOld->barcode,
                $assortmentBarcode1->barcode
            ],
            'article' => $assortmentOld->article
        ]));

        if ($userState !== 'admin') {
            $json->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
            $json->assertJsonValidationErrors('files');

            return;
        }

        $data = [
            'uuid' => $assortmentOld->uuid,
            'name' => $assortmentNew->name,
            'short_name' => $assortmentNew->short_name,
            'article' => $assortmentOld->article,
            'assortment_verify_status_id' => $assortmentOld->assortment_verify_status_id,
            'images' => [
                [
                    'uuid' => $image->uuid,
                    'path' => Storage::url($image->path),
                    'public_name' => static::ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE,
                ],
            ],
            'files' => [
                [
                    'uuid' => $file->uuid,
                    'path' => Storage::url($file->path),
                    'public_name' => 'file public name',
                ],
            ],
            'tags' => [
                $tag->name
            ],
            'barcodes' => [
                $assortmentBarcodeOld->barcode,
                $assortmentBarcode1->barcode
            ],
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('assortments', Arr::except($data, ['images', 'files', 'tags', 'barcodes']));

        Queue::assertPushed(ExportProductTo1C::class);
        Queue::assertPushed(UpdateCatalogAssortmentCountJob::class);
    }

    /**
     * @test
     */
    public function updateWithCatalogNotLastLevel()
    {
        $catalog = factory(Catalog::class)->state('has-children')->create();
        $assortmentOld = factory(Assortment::class)->create([
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
        ]);

        $assortmentNew = factory(Assortment::class)->make([
            'catalog_uuid' => $catalog->uuid
        ]);
        $user = factory(User::class)->state('admin')->create();

        $json = $this->be($user)->putJson("/api/assortments/{$assortmentOld->uuid}", $assortmentNew->only([
            'catalog_uuid',
        ]));
        $json
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('catalog_uuid');
    }

    /**
     * @test
     */
    public function updateViaCreate()
    {
        $assortmentOld = factory(Assortment::class)->create([
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_DECLINED,
        ]);

        $assortmentNew = factory(Assortment::class)->make([
            'barcodes' => $assortmentOld->barcodes,
        ]);

        $user = factory(User::class)->state('distribution-center')->create();
        $file = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
        ]);

        $json = $this->be($user)->postJson('/api/assortments', array_merge($assortmentNew->only([
            'catalog_uuid',
            'name',
            'short_name',
            'assortment_unit_id',
            'country_id',
            'okpo_code',
            'weight',
            'volume',
            'manufacturer',
            'ingredients',
            'description',
            'group_barcode',
            'temperature_min',
            'temperature_max',
            'production_standard_id',
            'production_standard_number',
            'is_storable',
            'shelf_life',
            'nds_percent',
            'assortment_brand_uuid',
        ]), [
            'images' => [
                [
                    'uuid' => $file->uuid,
                    'public_name' => static::ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE,
                ],
            ],
            'properties' => [
                //
            ],
            'barcodes' => $assortmentNew->barcodes->pluck('barcode')
        ]));

        $data = [
            'uuid' => $assortmentOld->uuid,
            'name' => $assortmentNew->name,
            'short_name' => $assortmentNew->short_name,
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
            'images' => [
                [
                    'uuid' => $file->uuid,
                    'path' => Storage::url($file->path),
                    'public_name' => static::ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE,
                ],
            ],
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('assortments', Arr::except($data, 'images'));

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $assortmentOld->catalog_uuid,
            'assortments_count' => 0,
        ]);

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $assortmentNew->catalog_uuid,
            'assortments_count' => 1,
        ]);
    }

    /**
     * @param string $barcode
     * @param bool $valid
     *
     * @test
     * @testWith ["1234567000000", true]
     *           ["9294599000000", true]
     *           ["9294599001000", false]
     *           ["9294599000001", false]
     */
    public function createWithKilogram(string $barcode, bool $valid)
    {
        $assortmentBarcode1 = factory(AssortmentBarcode::class)->make([
            'barcode' => $barcode
        ]);
        $assortmentNew = factory(Assortment::class)->make([
            'assortment_unit_id' => AssortmentUnit::ID_KILOGRAM
        ]);

        $user = factory(User::class)->state('distribution-center')->create();
        $file = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
        ]);

        $json = $this->be($user)->postJson('/api/assortments', array_merge($assortmentNew->only([
            'catalog_uuid',
            'name',
            'short_name',
            'assortment_unit_id',
            'country_id',
            'okpo_code',
            'weight',
            'volume',
            'manufacturer',
            'ingredients',
            'description',
            'group_barcode',
            'temperature_min',
            'temperature_max',
            'production_standard_id',
            'production_standard_number',
            'is_storable',
            'shelf_life',
            'nds_percent',
            'assortment_brand_uuid',
        ]), [
            'images' => [
                [
                    'uuid' => $file->uuid,
                    'public_name' => static::ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE,
                ],
            ],
            'properties' => [
                //
            ],
            'barcodes' => [
                $assortmentBarcode1->barcode,
            ],
        ]));

        if ($valid) {
            $json->assertSuccessful();
        } else {
            $body = json_decode($json->getContent(), true);
            $this->assertCount(1, $body['errors']);
            $this->assertArrayHasKey('barcodes.0', $body['errors']);
            $json->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param string $barcodeOld
     * @param string $barcodeNew
     *
     * @test
     * @testWith ["1234567000001", "1234567000002"]
     *           ["123456", "123456"]
     */
    public function createWithAlreadyExist(string $barcodeOld, string $barcodeNew)
    {
        $oldAssortment = factory(Assortment::class)->create([
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_DECLINED,
            'assortment_unit_id' => AssortmentUnit::ID_KILOGRAM
        ]);
        factory(AssortmentBarcode::class)->create([
            'barcode' => $barcodeOld,
            'assortment_uuid' => $oldAssortment->uuid
        ]);
        $assortmentBarcodeNew = factory(AssortmentBarcode::class)->make([
            'barcode' => $barcodeNew
        ]);
        $assortmentNew = factory(Assortment::class)->make();

        $user = factory(User::class)->state('distribution-center')->create();
        $file = factory(File::class)->create([
            'user_uuid' => $user->uuid,
            'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
        ]);

        $json = $this->be($user)->postJson('/api/assortments', array_merge($assortmentNew->only([
            'catalog_uuid',
            'name',
            'short_name',
            'assortment_unit_id',
            'country_id',
            'okpo_code',
            'weight',
            'volume',
            'manufacturer',
            'ingredients',
            'description',
            'group_barcode',
            'temperature_min',
            'temperature_max',
            'production_standard_id',
            'production_standard_number',
            'is_storable',
            'shelf_life',
            'nds_percent',
            'assortment_brand_uuid',
        ]), [
            'images' => [
                [
                    'uuid' => $file->uuid,
                    'public_name' => static::ASSORTMENT_IMAGE_PUBLIC_NAME_EXAMPLE,
                ],
            ],
            'properties' => [
                //
            ],
            'barcodes' => [
                $assortmentBarcodeNew->barcode,
            ],
        ]));

        $body = json_decode($json->getContent(), true);
        $json->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(1, $body['errors']);
        $this->assertArrayHasKey('barcodes.0', $body['errors']);
    }

    /**
     * @test
     */
    public function findByBarcode()
    {
        $assortment = factory(Assortment::class)->create();
        $barcode = $assortment->barcodes[0]->barcode;
        $self = factory(User::class)->state('store')->create();

        $json = $this->be($self)->getJson("/api/assortments/find-by-barcode?barcode={$barcode}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $assortment->uuid,
                'short_name' => $assortment->short_name,
            ],
        ]);
    }

    /**
     * @test
     * @testWith [true]
     *           [false]
     * @param bool $selfHasPrivatePrice
     */
    public function products(bool $selfHasPrivatePrice)
    {
        $user = factory(User::class)->state('distribution-center')->create();
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();
        $user->supplierRating()->create([
            'rating_type_id' => RatingType::ID_SUPPLIER,
            'value' => 5,
        ]);

        factory(ProductRequest::class)->create([
            'supplier_user_uuid' => $user->uuid,
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_DONE,
        ]);

        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);
        $privatePriceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => $selfHasPrivatePrice ? $self->uuid : factory(User::class)->state('store')->create()
        ]);

        $product = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'price' => 500
        ]);

        $commonProductPrice = 1000;
        $privateProductPrice = 100;
        $priceList->products()->attach([
            $product->uuid => ['price_new' => $commonProductPrice],
        ]);
        $privatePriceList->products()->attach([
            $product->uuid => ['price_new' => $privateProductPrice],
        ]);

        $json = $this->be($self)->getJson("/api/assortments/{$product->assortment_uuid}/products");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $product->uuid,
                    'user_uuid' => $user->uuid,
                    'user_organization_name' => $user->organization_name,
                    'user_supplier_rating' => 5,
                    'user_done_supplier_product_requests_count' => 1,
                    'assortment_uuid' => $product->assortment->uuid,
                    'assortment_name' => $product->assortment->name,
                    'quantum' => $product->quantum,
                    'min_quantum_in_order' => $product->min_quantum_in_order,
                    'price' => $selfHasPrivatePrice ? $privateProductPrice : $commonProductPrice,
                ],
            ],
        ]);
    }

    /**
     * Случай когда продукт есть в общем прайс листе, но нет в индивидуальном
     * @test
     */
    public function productsWhenPrivatePriceListExistAndNotContainProduct()
    {
        /** @var User $self */
        $user = factory(User::class)->state('distribution-center')->create();
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();

        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);
        factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => $self->uuid
        ]);

        /** @var Product $product */
        $product = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'price' => 500
        ]);
        $priceList->products()->attach([$product->uuid => ['price_new' => 500]]);

        $json = $this->be($self)->getJson("/api/assortments/{$product->assortment_uuid}/products");

        $json->assertSuccessful();
        $this->assertCount(0, $json->json('data'));
    }

    /**
     * Случай когда продукт доступен/скрыт для заказа
     * @testWith [true]
     *           [false]
     * @test
     * @param bool $is_active
     */
    public function productsTestIsActiveFlag(bool $is_active)
    {
        /** @var User $self */
        $user = factory(User::class)->state('distribution-center')->create();
        /** @var User $self */
        $self = factory(User::class)->state('store')->create();

        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
        ]);
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'is_active' => $is_active
        ]);
        $priceList->products()->attach([$product->uuid => ['price_new' => $product->price]]);

        $json = $this->be($self)->getJson("/api/assortments/{$product->assortment_uuid}/products");

        $json->assertSuccessful();
        $this->assertCount($is_active ? 1 : 0, $json->json('data'));
    }

    /**
     * @test
     * @testWith [true]
     *           [false]
     * @param bool $selfHasPrivatePrice
     */
    public function findProducts(bool $selfHasPrivatePrice)
    {
        $user = factory(User::class)->state('distribution-center')->create();
        $self = factory(User::class)->state('store')->create();

        $user->supplierRating()->create([
            'rating_type_id' => RatingType::ID_SUPPLIER,
            'value' => 5,
        ]);

        factory(ProductRequest::class)->create([
            'supplier_user_uuid' => $user->uuid,
            'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_DONE,
        ]);

        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => null
        ]);

        $privatePriceList = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'price_list_status_id' => 'current',
            'customer_user_uuid' => $selfHasPrivatePrice ? $self->uuid : factory(User::class)->state('store')->create()
        ]);

        $product = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'price' => 500
        ]);
        $commonProductPrice = 1000;
        $privateProductPrice = 100;
        $priceList->products()->attach([
            $product->uuid => ['price_new' => $commonProductPrice],
        ]);
        $privatePriceList->products()->attach([
            $product->uuid => ['price_new' => $privateProductPrice],
        ]);

        $request = http_build_query([
            'assortment_uuids' => [
                $product->assortment_uuid,
            ],
        ]);

        $json = $this->be($self)->getJson("/api/assortments/find-products?{$request}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $product->uuid,
                    'user_uuid' => $user->uuid,
                    'user_organization_name' => $user->organization_name,
                    'user_supplier_rating' => 5,
                    'user_done_supplier_product_requests_count' => 1,
                    'assortment_uuid' => $product->assortment->uuid,
                    'assortment_name' => $product->assortment->name,
                    'quantum' => $product->quantum,
                    'min_quantum_in_order' => $product->min_quantum_in_order,
                    'price' => $selfHasPrivatePrice ? $privateProductPrice : $commonProductPrice,
                    'delivery_weekdays' => $product->delivery_weekdays,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function verify()
    {
        $catalog = factory(Catalog::class)->state('has-parent')->create();
        $assortment = factory(Assortment::class)->create([
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_NEW,
            'catalog_uuid' => $catalog->uuid,
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/assortments/{$assortment->uuid}/verify", [
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
        ]);

        $data = [
            'uuid' => $assortment->uuid,
            'assortment_verify_status_id' => AssortmentVerifyStatus::ID_APPROVED,
            'short_name' => $assortment->short_name,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('assortments', $data);

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $catalog->uuid,
            'assortments_count' => 1,
        ]);

        $this->assertDatabaseHas('catalogs', [
            'uuid' => $catalog->catalog_uuid,
            'assortments_count' => 1,
        ]);
    }
}
