<?php

namespace Tests\Feature\API\Profile;

use App\Models\AssortmentProperty;
use App\Models\PriceList;
use App\Models\PriceListStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class PriceListProductTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $priceList = factory(PriceList::class)->create();
        $product = factory(Product::class)->create([
            'user_uuid' => $priceList->user_uuid,
            'is_active' => true,
        ]);

        $priceList->products()->attach($product);

        $self = $priceList->user;
        $json = $this->be($self)->getJson("/api/profile/price-lists/{$priceList->uuid}/products");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'product_uuid' => $product->uuid,
                    'price_recommended' => $product->price_recommended,
                    'is_active' => $product->is_active,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function indexWithFilterByAssortmentProperty()
    {
        $priceList = factory(PriceList::class)->create();
        /**
         * @var $product Product
         */
        $product = factory(Product::class)->create([
            'user_uuid' => $priceList->user_uuid,
            'is_active' => true,
        ]);

        $priceList->products()->attach($product);

        /**
         * @var $self User
         */
        $self = $priceList->user;
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
                    'operator' => '=',
                    'value' => $value1,
                ]
            ]
        ];
        $json = $this->be($self)->postJson("/api/profile/price-lists/{$priceList->uuid}/products", $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'product_uuid' => $product->uuid,
                    'price_recommended' => $product->price_recommended,
                    'is_active' => $product->is_active,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $priceList = factory(PriceList::class)->create();
        $product = factory(Product::class)->create(['user_uuid' => $priceList->user_uuid]);

        $priceList->products()->attach($product);

        $self = $priceList->user;
        $json = $this->be($self)->putJson("/api/profile/price-lists/{$priceList->uuid}/products/{$product->uuid}", [
            'price_new' => 31337,
        ]);

        $json->assertSuccessful();
        $this->assertDatabaseHas('price_list_product', [
            'price_list_uuid' => $priceList->uuid,
            'product_uuid' => $product->uuid,
            'price_new' => 31337,
        ]);
    }

    /**
     * @test
     */
    public function batchUpdate()
    {
        /**
         * @var $priceList PriceList
         * @var $product1 Product
         * @var $product2 Product
         */
        $priceList = factory(PriceList::class)->create();
        $product1 = factory(Product::class)->create(['user_uuid' => $priceList->user_uuid]);
        $product2 = factory(Product::class)->create(['user_uuid' => $priceList->user_uuid]);

        $priceNew1 = 31337;
        $priceNew2 = 31232;

        $priceList->products()->saveMany([$product1, $product2]);

        $self = $priceList->user;
        $json = $this->be($self)->postJson("/api/profile/price-lists/{$priceList->uuid}/products/batch-update", [
            'products' => [
                [
                    'product_uuid' => $product1->uuid,
                    'price_new' => $priceNew1,
                ],
                [
                    'product_uuid' => $product2->uuid,
                    'price_new' => $priceNew2,
                ],
            ]
        ]);

        $json->assertSuccessful();
        $this->assertDatabaseHas('price_list_product', [
            'price_list_uuid' => $priceList->uuid,
            'product_uuid' => $product1->uuid,
            'price_new' => $priceNew1,
        ]);
        $this->assertDatabaseHas('price_list_product', [
            'price_list_uuid' => $priceList->uuid,
            'product_uuid' => $product2->uuid,
            'price_new' => $priceNew2,
        ]);
    }

    /**
     * @test
     */
    public function synchronize()
    {
        /** @var PriceList $currentPriceList */
        $currentPriceList = factory(PriceList::class)->create();
        $products = factory(Product::class, 5)->create([
            'user_uuid' => $currentPriceList->user_uuid,
            'price' => 500
        ]);

        $currentPriceList->products()->attach($products->mapWithKeys(function ($i) {
            return [$i['uuid'] => ['price_new' => 1000]];
        })->all());

        $currentPriceList->price_list_status_id = PriceListStatus::CURRENT;
        $currentPriceList->save();

        $priceList = factory(PriceList::class)->create([
            'user_uuid' => $currentPriceList->user_uuid,
            'customer_user_uuid' => $currentPriceList->customer_user_uuid
        ]);

        $self = $priceList->user;
        $json = $this->be($self)->postJson("/api/profile/price-lists/{$priceList->uuid}/products/synchronize");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'synchronized' => 5,
            ],
        ]);

        $products->each(function (Product $product) use ($priceList) {
            $this->assertDatabaseHas('price_list_product',
                [
                    'price_list_uuid' => $priceList->uuid,
                    'product_uuid' => $product->uuid,
                    'price_new' => 1000
                ]
            );
        });
    }

    /**
     * @test
     */
    public function synchronizeForStore()
    {
        /**
         * @var User $self
         * @var PriceList $priceList
         */
        $self = factory(User::class)->state('store')->create();
        $priceList = factory(PriceList::class)->create(['user_uuid' => $self->uuid]);
        $created = factory(Product::class, 5)->create(['user_uuid' => $priceList->user_uuid]);

        // Два добавим в матрицу
        $self->assortmentMatrix()->sync([$created[0]->assortment_uuid, $created[1]->assortment_uuid]);

        // Три добавим в прайс
        $priceList->products()->sync([$created[2]->uuid, $created[3]->uuid]);

        $json = $this->be($self)->postJson("/api/profile/price-lists/{$priceList->uuid}/products/synchronize");
        $json->assertSuccessful()->assertJson([
            'data' => [
                'synchronized' => 4,
            ],
        ]);

        // Два должны быть удалены и два добавлены
        $this->assertDatabaseHas('price_list_product', ['product_uuid' => $created[0]->uuid]);
        $this->assertDatabaseHas('price_list_product', ['product_uuid' => $created[1]->uuid]);
        $this->assertDatabaseMissing('price_list_product', ['product_uuid' => $created[2]->uuid]);
        $this->assertDatabaseMissing('price_list_product', ['product_uuid' => $created[3]->uuid]);
    }
}
