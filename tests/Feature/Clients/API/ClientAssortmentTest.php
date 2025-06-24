<?php

namespace Tests\Feature\Clients\API;

use App\Models\Assortment;
use App\Models\AssortmentProperty;
use App\Models\Client;
use App\Models\ClientShoppingList;
use App\Models\LoyaltyCardType;
use App\Models\Product;
use App\Models\Promo\PromoDescriptionFirstOrder;
use App\Models\PromoYellowPrice;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCaseNotificationsFake;

class ClientAssortmentTest extends TestCaseNotificationsFake
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
        $assortment = factory(Assortment::class)->create();

        $self = factory(Client::class)->create();
        $self->favoriteAssortments()->attach($assortment);

        $json = $this->be($self)->getJson('/clients/api/assortments?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $assortment->uuid,
            'manufacturer' => $assortment->manufacturer,
            'is_favorite' => true,
        ]);
    }

    /**
     * @test
     */
    public function indexWithoutUser()
    {
        $assortment = factory(Assortment::class)->create();
        $json = $this->getJson('/clients/api/assortments?per_page=1000');
        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $assortment->uuid,
            'manufacturer' => $assortment->manufacturer
        ]);
    }

    /**
     * @test
     */
    public function indexWithFilterByTag()
    {
        $assortment = factory(Assortment::class)->create();

        $tagOne = factory(Tag::class)->create();
        $tagTwo = factory(Tag::class)->create();

        $assortment->tags()->attach($tagOne);
        $assortment->tags()->attach($tagTwo);

        $self = factory(Client::class)->create();
        $self->favoriteAssortments()->attach($assortment);

        $data = ['where' => [['tags', 'in', $tagOne->name]]];
        // Протестим еще и фильтрацию тегов
        $json = $this->be($self)->json('get', '/clients/api/assortments', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $assortment->uuid,
                    'manufacturer' => $assortment->manufacturer,
                    'is_favorite' => true,
                    'tags' => [$tagOne->name, $tagTwo->name]
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function indexWithFilterByProperty()
    {
        /**
         * @var $assortment Assortment
         */
        $assortment = factory(Assortment::class)->create();

        $propertyValue = $this->faker->word;

        /**
         * @var $assortmentProperty AssortmentProperty
         */
        $assortmentProperty = factory(AssortmentProperty::class)->make();
        $assortment->assortmentProperties()->saveMany(
            [
                $assortmentProperty
            ],
            [
                ['value' => $propertyValue],
            ]
        );

        /** @var Client $self */
        $self = factory(Client::class)->create();
        $self->favoriteAssortments()->attach($assortment);
        $data = [
            'where' => [[
                'properties',
                'in',
                [[$assortmentProperty->name, 'xyz'], [$propertyValue, 'xyz']]
            ]]
        ];

        $json = $this->be($self)->json('get', '/clients/api/assortments?' . http_build_query($data));
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $assortment->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     *
     * @testWith [true]
     *           [false]
     */
    public function show(bool $useYellowDiscount)
    {
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        /** @var LoyaltyCardType $loyaltyCardType */
        $loyaltyCardType = factory(LoyaltyCardType::class)->create();
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();
        $assortment->tags()->attach($tag->uuid);

        $assortment->rating()->create(['value' => 5]);
        $store->assortmentMatrix()->attach($assortment);
        $store->loyaltyCardTypes()->attach($loyaltyCardType);

        /** @var Client $self */
        $self = factory(Client::class)->create();
        $self->favoriteAssortments()->attach($assortment);
        $self->getShoppingCart()->add($assortment->uuid, 2)->save();

        /** @var User $user */
        $user = factory(User::class)->create(['user_type_id' => UserType::ID_SUPPLIER]);

        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $user->uuid,
            'quantity' => 10,
            'is_active' => true,
        ]);

        // Discount
        if ($useYellowDiscount) {
            /** @var PromoYellowPrice $promo */
            $promo = factory(PromoYellowPrice::class)->create([
                'assortment_uuid' => $assortment->uuid,
                'is_enabled' => 1,
                'start_at' => now()->subWeek(),
                'end_at' => now()->addWeek(),
            ]);
            $promo->stores()->sync([$user->uuid]);
        } else {
            Config::set('app.order.price.first_order_discount_resolver_config.discount_percent', 10);
        }

        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $store->uuid,
            'quantity' => 100,
            'is_active' => true,
        ]);

        $request = http_build_query([
            'store_uuid' => $user->uuid,
        ]);

        $shoppingList = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid
        ]);
        $shoppingList->assortments()->sync([$assortment->uuid]);

        $json = $this->be($self)->getJson("/clients/api/assortments/{$assortment->uuid}?" . $request);

        $expectedData = [
            'uuid' => $assortment->uuid,
            'catalog_uuid' => $assortment->catalog->uuid,
            'catalog_name' => $assortment->catalog->name,
            'name' => $assortment->name,
            'short_name' => $assortment->short_name,
            'country_id' => $assortment->country_id,
            'weight' => $assortment->weight,
            'volume' => $assortment->volume,
            'manufacturer' => $assortment->manufacturer,
            'ingredients' => $assortment->ingredients,
            'description' => $assortment->description,
            'temperature_min' => $assortment->temperature_min,
            'temperature_max' => $assortment->temperature_max,
            'production_standard_id' => $assortment->production_standard_id,
            'production_standard_number' => $assortment->production_standard_number,
            'shelf_life' => $assortment->shelf_life,
            'rating' => 5,
            'stores' => [
                [
                    'uuid' => $store->uuid,
                    'brand_name' => $store->organization_name,
                    'loyalty_card_types' => [
                        [
                            'uuid' => $loyaltyCardType->uuid,
                        ],
                    ],
                    'products_quantity' => $product2->quantity
                ],
            ],
            'is_favorite' => true,
            'tags' => [
                $tag->name
            ],
            'current_price' => $product->price,
            'products_quantity' => $product->quantity,
            'user_shopping_lists' => [
                [
                    'uuid' => $shoppingList->uuid,
                    'name' => $shoppingList->name
                ]
            ],
            'quantity_in_client_cart' => 2,
        ];

        if ($useYellowDiscount) {
            $expectedData['discount_type'] =  PromoYellowPrice::class;
            $expectedData['discount_type_name'] = 'Желтые ценники';
        }

        $json->assertSuccessful()->assertJson([
            'data' => $expectedData,
        ]);
    }

    /**
     * @test
     */
    public function search()
    {
        $assortment = factory(Assortment::class)->create([
            'name' => 'Серая кошка и мышка',
        ]);

        $self = factory(Client::class)->create();
        $json = $this->be($self)->getJson('/clients/api/assortments/search?phrase=кошки+сер');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $assortment->uuid,
                    'short_name' => $assortment->short_name,
                ],
            ],
        ]);
    }
}
