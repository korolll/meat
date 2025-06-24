<?php


namespace Tests\Feature\Clients\API\Profile;


use App\Http\Resources\Clients\API\Profile\PromotionInTheShopResource;
use App\Jobs\DeleteExpiredClientPurchase;
use App\Models\Assortment;
use App\Models\AssortmentProperty;
use App\Models\Client;
use App\Models\Product;
use App\Models\PromotionInTheShopLastPurchase;
use App\Models\User;
use App\Services\Management\Profiles\Promotions\InTheShopService;
use App\Services\Management\Profiles\Promotions\InTheShopServiceContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCaseNotificationsFake;

class PromotionInTheShopTest extends TestCaseNotificationsFake
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
     * @return string
     */
    public function testIndex()
    {
        $propUuid = $this->faker->uuid;

        Config::set('app.promotions.in_the_shop.assortment_property_uuid', $propUuid);
        Config::set('app.promotions.in_the_shop.offer_delay', $this->faker->numberBetween(1, 10));
        Config::set('app.promotions.in_the_shop.tracking_period', $this->faker->numberBetween(10, 100));

        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        $self = factory(Client::class)->create();
        $property = factory(AssortmentProperty::class)->create([
            'uuid' => $propUuid
        ]);

        $assortments = $this->fillAssortments($property, $self, $store);
        $promotionService = app(InTheShopServiceContract::class);
        $promotionService->activate($self, $store);

        $response = $this->be($self)->getJson('/clients/api/profile/promotion/in-the-shop?store_uuid=' . $store->uuid);
        $response->assertSuccessful();
        $response->assertJson(['data' => ['store_uuid' => $store->uuid]]);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data.products', 6)
        );
        $response->assertJsonFragment(['uuid' => $assortments->get(0)->uuid]);
        $response->assertJsonFragment(['uuid' => $assortments->get(1)->uuid]);
        $response->assertJsonFragment(['uuid' => $assortments->get(2)->uuid]);
        $response->assertJsonFragment(['uuid' => $assortments->get(3)->uuid]);
        $response->assertJsonFragment(['uuid' => $assortments->get(4)->uuid]);
        $response->assertJsonFragment(['uuid' => $assortments->get(5)->uuid]);

        return 'a';
    }

    /**
     * @test
     */
    public function testStore()
    {
        $propUuid = $this->faker->uuid;
        Config::set('app.promotions.in_the_shop.assortment_property_uuid', $propUuid);
        Config::set('app.promotions.in_the_shop.offer_delay', $this->faker->numberBetween(1, 10));
        Config::set('app.promotions.in_the_shop.tracking_period', $this->faker->numberBetween(10, 100));

        $store = factory(User::class)->state('store')->create();
        $self = factory(Client::class)->create();
        $property = factory(AssortmentProperty::class)->create([
            'uuid' => $propUuid
        ]);

        $assortments = $this->fillAssortments($property, $self, $store);
        $json = $this->be($self)->postJson('/clients/api/profile/promotion/in-the-shop', [
            'store_uuid' => $store->uuid,
        ]);

        $service = app(InTheShopService::class);
        $promotion = $service->getActivated($self, $store);

        /** ASSERTS */
        $json->assertSuccessful();
        $this->assertDatabaseHas('client_promotions', [
            'promotion_type' => InTheShopService::PROMOTION_KEY,
            'user_uuid' => $store->uuid,
            'client_uuid' => $self->uuid
        ]);

        foreach ($assortments as $assortment) {
            $this->assertDatabaseHas('promotion_in_the_shop_assortments', [
                'client_promotion_uuid' => $promotion->uuid,
                'assortment_uuid' => $assortment->uuid,
            ]);
        }

        return PromotionInTheShopResource::make($promotion);
    }

    /**
     *
     */
    public function testDeleteExpiredClientPurchase()
    {
        $propUuid = $this->faker->uuid;
        Config::set('app.promotions.in_the_shop.assortment_property_uuid', $propUuid);
        Config::set('app.promotions.in_the_shop.offer_delay', $this->faker->numberBetween(1, 10));
        Config::set('app.promotions.in_the_shop.tracking_period', $this->faker->numberBetween(10, 100));

        $store = factory(User::class)->state('store')->create();
        $self = factory(Client::class)->create();
        $property = factory(AssortmentProperty::class)->create([
            'uuid' => $propUuid
        ]);

        $this->fillAssortments($property, $self, $store);

        $purchases = PromotionInTheShopLastPurchase::all();

        foreach ($purchases as $purchase) {
            $purchase->delete_after = date('c', strtotime('-1 day'));
            $purchase->save();
        }

        DeleteExpiredClientPurchase::dispatch();

        $this->assertDatabaseCount('promotion_in_the_shop_last_purchases', 0);
    }

    /**
     * @param \App\Models\AssortmentProperty $assortmentProperty
     *
     * @return \Illuminate\Support\Enumerable
     */
    private function createAssortmentsWithNewProperty(AssortmentProperty $assortmentProperty): Enumerable
    {
        $pivotValue = $this->faker->word;
        Config::set('app.promotions.in_the_shop.property_new', $pivotValue);

        return factory(Assortment::class, 2)
            ->create()
            ->each(function (Assortment $assortment) use ($assortmentProperty, $pivotValue) {
                $assortment->assortmentProperties()
                    ->withPivotValue('value', $pivotValue)
                    ->save($assortmentProperty);
            });
    }

    /**
     * @param \App\Models\AssortmentProperty $assortmentProperty
     *
     * @return \Illuminate\Support\Enumerable
     */
    private function createAssortmentsWithSaleProperty(AssortmentProperty $assortmentProperty): Enumerable
    {
        $pivotValue = $this->faker->word;
        Config::set('app.promotions.in_the_shop.property_sale', $pivotValue);

        return factory(Assortment::class, 2)
            ->create()
            ->each(function (Assortment $assortment) use ($assortmentProperty, $pivotValue) {
                $assortment->assortmentProperties()
                    ->withPivotValue('value', $pivotValue)
                    ->save($assortmentProperty);
            });
    }

    /**
     * @param \Illuminate\Support\Enumerable $assortments
     * @param \App\Models\Client             $client
     */
    private function addAssortmentsToPurchases(Enumerable $assortments, Client $client): void
    {
        foreach ($assortments as $assortment) {
            PromotionInTheShopLastPurchase::factory()
                ->stateClient($client)
                ->stateAssortment($assortment)
                ->stateNotBoughtLongTime()
                ->create();
        }
    }

    /**
     * @param \Illuminate\Support\Enumerable $assortments
     * @param \App\Models\User               $store
     */
    private function addAssortmentsToProducts(Enumerable $assortments, User $store): void
    {
        foreach ($assortments as $assortment) {
            factory(Product::class)->create([
                'user_uuid' => $store->uuid,
                'assortment_uuid' => $assortment->uuid,
            ]);
        }
    }

    /**
     * @param $property
     * @param $self
     * @param $store
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private function fillAssortments($property, $self, $store): Enumerable
    {
        $assortmentsToPurchase = factory(Assortment::class, 2)->create();
        $assortmentsWithNewProperty = $this->createAssortmentsWithNewProperty($property);
        $assortmentsWithSaleProperty = $this->createAssortmentsWithSaleProperty($property);

        $assortments = $assortmentsToPurchase
            ->merge($assortmentsWithNewProperty)
            ->merge($assortmentsWithSaleProperty);

        $this->addAssortmentsToPurchases($assortmentsToPurchase, $self);
        $this->addAssortmentsToProducts($assortments, $store);

        return $assortments;
    }
}
