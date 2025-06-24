<?php

namespace Tests\Feature\Clients\API;

use App\Models\Assortment;
use App\Models\Catalog;
use App\Models\Client;
use App\Models\File;
use App\Models\LoyaltyCardType;
use App\Models\Product;
use App\Models\PromoYellowPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class StoreTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Auth::shouldUse('api-clients');
    }

    /**
     * @param string        $field
     * @param string        $operator
     * @param callable      $getValue
     *
     * @test
     * @dataProvider indexDataProvider
     */
    public function index(string $field, string $operator, callable $getValue)
    {
        /** @var User $store */
        $store = factory(User::class)
            ->states([
                'store',
                'has-image',
            ])
            ->create();

        $file = factory(File::class)->create([
            'user_uuid' => $store->uuid
        ]);
        $loyaltyCardTypes = factory(LoyaltyCardType::class)->times(2)->create([
            'logo_file_uuid' => $file->uuid
        ]);
        $store->loyaltyCardTypes()->attach($loyaltyCardTypes);
        /** @var Client $self */
        $self = factory(Client::class)->create();
        // Избранный магаз
        $self->favoriteStores()->attach($store->uuid);

        $query = [
            'where' => [[
                $field,
                $operator,
                $getValue($store)
            ]],
            'order_by' => ['address_latitude' => 'ASC']
        ];

        $json = $this->be($self)->json('get', '/clients/api/stores', $query);
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $store->uuid,
                    'is_favorite' => true,
                    'loyalty_card_types' => [
                        ['uuid' => $loyaltyCardTypes[0]->uuid],
                        ['uuid' => $loyaltyCardTypes[1]->uuid],
                    ],
                    'address' => $store->address,
                    'phone' => $store->phone,
                    'has_parking' => $store->has_parking,
                    'has_ready_meals' => $store->has_ready_meals,
                    'has_atms' => $store->has_atms,
                    'image' => [
                        'uuid' => $store->image->uuid,
                        'thumbnails' => [],
                        'path' => Storage::url($store->image->path),
                    ],
                ],
            ],
        ]);
    }

    /**
     *
     */
    public function testIndexWithoutUser()
    {
        /** @var User $store */
        $store = factory(User::class)
            ->states([
                'store',
                'has-image',
            ])
            ->create();

        $file = factory(File::class)->create([
            'user_uuid' => $store->uuid
        ]);
        $loyaltyCardTypes = factory(LoyaltyCardType::class)->times(2)->create([
            'logo_file_uuid' => $file->uuid
        ]);
        $store->loyaltyCardTypes()->attach($loyaltyCardTypes);

        $json = $this->json('get', '/clients/api/stores');
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $store->uuid,
                    'is_favorite' => false,
                    'loyalty_card_types' => [
                        ['uuid' => $loyaltyCardTypes[0]->uuid],
                        ['uuid' => $loyaltyCardTypes[1]->uuid],
                    ],
                    'address' => $store->address,
                    'phone' => $store->phone,
                    'has_parking' => $store->has_parking,
                    'has_ready_meals' => $store->has_ready_meals,
                    'has_atms' => $store->has_atms,
                    'image' => [
                        'uuid' => $store->image->uuid,
                        'thumbnails' => [],
                        'path' => Storage::url($store->image->path),
                    ],
                ],
            ],
        ]);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function indexDataProvider(): array
    {
        return [
            [
                'loyalty_card_type_uuid',
                'in',
                function (User $store) {
                    return $store->loyaltyCardTypes[0]->uuid;
                },
            ],
            [
                'is_favorite',
                '=',
                function (User $store) {
                    return 1;
                }
            ],
            [
                'address_latitude',
                '=',
                function (User $store) {
                    return $store->address_latitude;
                }
            ],
            [
                'has_atms',
                '=',
                function (User $store) {
                    return $store->has_atms;
                }
            ]
        ];
    }

    /**
     * @test
     */
    public function show()
    {
        /** @var User $store */
        $store = factory(User::class)
            ->states([
                'store',
                'has-image',
            ])
            ->create();

        $loyaltyCardTypes = factory(LoyaltyCardType::class)->times(2)->create();
        $store->loyaltyCardTypes()->attach($loyaltyCardTypes);

        $self = factory(Client::class)->create();
        $self->favoriteStores()->attach($store);

        $json = $this->be($self)->getJson(sprintf('/clients/api/stores/%s', $store->uuid));

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $store->uuid,
                'brand_name' => $store->brand_name,
                'organization_name' => $store->organization_name,
                'loyalty_card_types' => $loyaltyCardTypes->map->only(['uuid'])->all(),
                'address' => $store->address,
                'work_hours_from' => $store->work_hours_from,
                'work_hours_till' => $store->work_hours_till,
                'phone' => $store->phone,
                'has_parking' => $store->has_parking,
                'has_ready_meals' => $store->has_ready_meals,
                'has_atms' => $store->has_atms,
                'image' => [
                    'uuid' => $store->image->uuid,
                    'thumbnails' => [],
                    'path' => Storage::url($store->image->path),
                ],
                'is_favorite' => true
            ]
        ]);
    }

    /**
     * @test
     */
    public function findNearbyStores()
    {
        /** @var User $store1 */
        $store1 = factory(User::class)->state('store')->create([
            'address_latitude' => 12.123333,
            'address_longitude' => 12.123333,
        ]);

        factory(User::class)->state('store')->create([
            'address_latitude' => 15.123333,
            'address_longitude' => 15.123333,
        ]);

        $self = factory(Client::class)->create();
        $json = $this->be($self)->json('GET', '/clients/api/stores/find-nearby', [
            'latitude' => 12.133333,
            'longitude' => 12.133333,
            'limit' => 3,
            'max_distance_meters' => 2000
        ]);

        $json->assertSuccessful()->assertJson([
            'data' => [[
                'uuid' => $store1->uuid,
                'distance' => 1551
            ]]
        ]);
    }

    /**
     * @test
     */
    public function showAssortments()
    {
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->state('has-image')->create();
        $assortment->rating()->create(['value' => 5]);
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        $store->assortmentMatrix()->attach($assortment);

        $catalog = $assortment->catalog;
        /** @var Catalog $parentCatalog */
        $parentCatalog = factory(Catalog::class)->create([
            'catalog_uuid' => null
        ]);
        $catalog->catalog_uuid = $parentCatalog->uuid;
        $catalog->save();

        /** @var Client $self */
        $self = factory(Client::class)->create();
        $self->favoriteAssortments()->attach($assortment);
        $cart = $self->getShoppingCart();
        $cart->add($assortment->uuid, 1);
        $cart->save();

        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $store->uuid,
            'is_active' => true,
        ]);

        /** @var PromoYellowPrice $yellowPrice */
        $yellowPrice = factory(PromoYellowPrice::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'is_enabled' => true,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);
        $yellowPrice->stores()->sync([
            $store->uuid
        ]);

        $query = [
            'where' => [[
                'is_favorite',
                '=',
                '1'
            ], [
                'catalog_with_children_uuid',
                'in',
                $parentCatalog->uuid
            ], [
                'has_yellow_price',
                '=',
                1
            ]]
        ];

        $json = $this->be($self)->json('GET', sprintf('/clients/api/stores/%s/assortments', $store->uuid), $query);
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'name' => $assortment->name,
                    'weight' => $assortment->weight,
                    'volume' => $assortment->volume,
                    'images' => $assortment->images->map(function (File $file) {
                        return [
                            'uuid' => $file->uuid,
                            'path' => Storage::url($file->path),
                        ];
                    })->all(),
                    'rating' => 5,
                    'is_favorite' => true,
                    'current_price' => $product->price,
                    'quantity_in_client_cart' => 1
                ]
            ]
        ]);
    }
}
