<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientShoppingList;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\Product;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Models\PromoYellowPrice;
use App\Models\User;
use App\Models\UserType;
use App\Services\Money\MoneyHelper;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class ShoppingListTest extends TestCaseNotificationsFake
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
     * @test
     */
    public function index()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientShoppingList $shoppingList */
        $shoppingList = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid,
        ]);
        /** @var ClientShoppingList $shoppingList2 */
        $shoppingList2 = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid,
        ]);

        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'user_uuid' => $store->uuid
        ]);
        $assortment = $product->assortment;
        $shoppingList->assortments()->sync([$assortment->uuid]);
        $shoppingList2->assortments()->sync([$assortment->uuid]);

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

        $json = $this->be($self)->getJson('/clients/api/profile/shopping-lists?store_uuid=' . $store->uuid);

        $json->assertJson([
            'data' => [
                [
                    'uuid' => $shoppingList->uuid,
                    'assortments' => [[
                        'uuid' => $assortment->uuid,
                        'price_with_discount' => $yellowPrice->price,
                        'discount_type' => PromoYellowPrice::class
                    ]]
                ],
                [
                    'uuid' => $shoppingList2->uuid,
                    'assortments' => [[
                        'uuid' => $assortment->uuid,
                        'price_with_discount' => $yellowPrice->price,
                        'discount_type' => PromoYellowPrice::class
                    ]]
                ]
            ]
        ]);
    }

    /**
     * @param $name
     * @param $quantity
     *
     * @test
     * @testWith ["dummy", 4]
     */
    public function store($name, $quantity)
    {
        $self = factory(Client::class)->create();
        $assortment = factory(Assortment::class)->create();

        $json = $this->be($self)->postJson('/clients/api/profile/shopping-lists', [
            'name' => $name,
            'assortments' => [
                [
                    'uuid' => $assortment->uuid,
                    'quantity' => $quantity,
                ]
            ]
        ]);

        $json->assertJson([
            'data' => [
                'name' => $name,
                'assortments' => [
                    [
                        'name' => $assortment->name,
                        'quantity' => $quantity,
                    ]
                ],
            ]
        ]);

        $this->assertDatabaseHas('client_shopping_lists', [
            'client_uuid' => $self->uuid,
            'name' => $name,
        ]);
    }

    /**
     * @param $name
     * @param $quantity
     *
     * @test
     * @testWith ["dummy", 49]
     */
    public function show($name, $quantity)
    {
        $store = factory(User::class)->state('store')->create();
        $self = factory(Client::class)->create();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $assortment->rating()->create(['value' => 5]);
        $shoppingList = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid,
            'name' => $name,
        ]);

        $store->assortmentMatrix()->attach($assortment);

        /** @var User $user */
        $user = factory(User::class)->create(['user_type_id' => UserType::ID_SUPPLIER]);
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $user->uuid,
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
            $user->uuid
        ]);

        $request = http_build_query([
            'store_uuid' => $user->uuid,
        ]);

        /** @var File $image */
        $image = factory(File::class)->create();
        $assortment->images()->attach($image, ['file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE]);
        $shoppingList->assortments()->attach($assortment, ['quantity' => $quantity]);

        $json = $this->be($self)->getJson("/clients/api/profile/shopping-lists/{$shoppingList->uuid}?"  . $request);

        $json->assertJson([
            'data' => [
                'uuid' => $shoppingList->uuid,
                'name' => $name,
                'assortments' => [
                    [
                        'uuid' => $assortment->uuid,
                        'name' => $assortment->name,
                        'rating' => 5,
                        'quantity' => $quantity,
                        'current_price' => $product->price,
                        'images' => [
                            [
                                'uuid' => $image->uuid,
                                'path' => Storage::url($image->path)
                            ],
                        ],

                        'price_with_discount' => $yellowPrice->price,
                        'discount_type' => PromoYellowPrice::class
                    ]
                ],
            ]
        ]);
    }

    /**
     * @return void
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function testShowWithFakeDiscount()
    {
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $assortment->rating()->create(['value' => 5]);
        /** @var ClientShoppingList $shoppingList */
        $shoppingList = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid,
        ]);

        $store->assortmentMatrix()->attach($assortment);

        /** @var User $user */
        $user = factory(User::class)->create(['user_type_id' => UserType::ID_SUPPLIER]);
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $user->uuid,
            'is_active' => true,
        ]);

        $diverseFoodDiscount = $this->faker->randomFloat(2, 5, 15);
        PromoDiverseFoodClientDiscount::factory()->createOne([
            'client_uuid' => $self->uuid,
            'discount_percent' => $diverseFoodDiscount,
            'start_at' => now()->startOfMonth(),
            'end_at' => now()->endOfMonth(),
        ]);

        $request = http_build_query([
            'store_uuid' => $user->uuid,
        ]);

        $quantity = 10;
        $newPrice = MoneyHelper::valueWithDiscount($diverseFoodDiscount, $product->price);
        $newPrice = MoneyHelper::toFloat($newPrice);

        $shoppingList->assortments()->attach($assortment, ['quantity' => $quantity]);

        $json = $this->be($self)->getJson("/clients/api/profile/shopping-lists/{$shoppingList->uuid}?"  . $request);

        $json->assertJson([
            'data' => [
                'uuid' => $shoppingList->uuid,
                'assortments' => [
                    [
                        'uuid' => $assortment->uuid,
                        'name' => $assortment->name,
                        'rating' => 5,
                        'quantity' => $quantity,
                        'current_price' => $newPrice,
                        'images' => [],
                    ]
                ],
            ]
        ]);
    }

    /**
     * @param $name
     * @param $quantity
     * @param $quantityNew
     *
     * @test
     * @testWith ["dummy", 40, 10]
     */
    public function update($name, $quantity, $quantityNew)
    {
        $self = factory(Client::class)->create();
        $assortment = factory(Assortment::class)->create();
        $shoppingList = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid,
            'name' => $name,
        ]);

        $shoppingList->assortments()->attach($assortment, ['quantity' => $quantity]);

        $json = $this->be($self)->putJson("/clients/api/profile/shopping-lists/{$shoppingList->uuid}", [
            'name' => $name,
            'assortments' => [
                [
                    'uuid' => $assortment->uuid,
                    'quantity' => $quantityNew,
                ],
            ],
        ]);

        $json->assertJson([
            'data' => [
                'uuid' => $shoppingList->uuid,
                'name' => $name,
                'assortments' => [
                    [
                        'name' => $assortment->name,
                        'quantity' => $quantityNew,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $self = factory(Client::class)->create();
        $shoppingList = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid,
            'name' => 'Dummy',
        ]);


        $json = $this->be($self)->deleteJson("/clients/api/profile/shopping-lists/{$shoppingList->uuid}");

        $json->assertSuccessful();
    }
}
