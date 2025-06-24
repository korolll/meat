<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Assortment;
use App\Models\Catalog;
use App\Models\Client;
use App\Models\ClientActivePromoFavoriteAssortment;
use App\Models\ClientPromoFavoriteAssortmentVariant;
use App\Models\DiscountForbiddenAssortment;
use App\Models\DiscountForbiddenCatalog;
use App\Models\LoyaltyCard;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PromoFavoriteAssortmentSetting;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCaseNotificationsFake;

class PromoFavoriteAssortmentVariantTest extends TestCaseNotificationsFake
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
     *
     */
    public function testIndex()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientPromoFavoriteAssortmentVariant $variant */
        $variant = ClientPromoFavoriteAssortmentVariant::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson("/clients/api/profile/promo-favorite-assortment-variants");
        $json->assertSuccessful();
        $json->assertJson([
            'data' => [[
                'uuid' => $variant->uuid
            ]]
        ]);
    }

    /**
     *
     */
    public function testActivateDiscountOutOfDate()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientPromoFavoriteAssortmentVariant $variant */
        $variant = ClientPromoFavoriteAssortmentVariant::factory()->createOne([
            'client_uuid' => $self->uuid,
            'can_be_activated_till' => now()->subDay()
        ]);

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $json = $this->be($self)->postJson("/clients/api/profile/promo-favorite-assortment-variants/{$variant->uuid}/activate", [
            'assortment_uuid' => $assortment->uuid
        ]);
        $json->assertStatus(Response::HTTP_BAD_REQUEST);
        $json->assertJson([
           'message' => 'Discount is out of date'
        ]);
    }

    /**
     * @param bool $banCatalog
     * @return void
     *
     * @testWith [false]
     *           [true]
     */
    public function testActivateDiscountBannedAssortment(bool $banCatalog = false)
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientPromoFavoriteAssortmentVariant $variant */
        $variant = ClientPromoFavoriteAssortmentVariant::factory()->createOne([
            'client_uuid' => $self->uuid,
            'can_be_activated_till' => now()->subDay()
        ]);

        /** @var Catalog $catalogParent */
        $catalogParent = factory(Catalog::class)->create();
        /** @var Catalog $catalogChild */
        $catalogChild = factory(Catalog::class)->create([
            'catalog_uuid' => $catalogParent->uuid
        ]);

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'catalog_uuid' => $catalogChild->uuid
        ]);
        if ($banCatalog) {
            DiscountForbiddenCatalog::factory()->createOne([
                'catalog_uuid' => $catalogParent->uuid
            ]);
        } else {
            DiscountForbiddenAssortment::factory()->createOne([
                'assortment_uuid' => $assortment->uuid
            ]);
        }

        $json = $this->be($self)->postJson("/clients/api/profile/promo-favorite-assortment-variants/{$variant->uuid}/activate", [
            'assortment_uuid' => $assortment->uuid
        ]);
        $json->assertStatus(Response::HTTP_BAD_REQUEST);
        $json->assertJson([
           'message' => 'Assortment is forbidden for discount'
        ]);
    }

    /**
     *
     */
    public function testActivateDiscountActivatedExist()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientPromoFavoriteAssortmentVariant $variant */
        $variant = ClientPromoFavoriteAssortmentVariant::factory()->createOne([
            'client_uuid' => $self->uuid,
            'can_be_activated_till' => now()->addWeek()
        ]);

        PromoFavoriteAssortmentSetting::factory()->createOne();
        ClientActivePromoFavoriteAssortment::factory()->createOne([
            'client_uuid' => $self->uuid,
        ]);

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $json = $this->be($self)->postJson("/clients/api/profile/promo-favorite-assortment-variants/{$variant->uuid}/activate", [
            'assortment_uuid' => $assortment->uuid
        ]);
        $json->assertStatus(Response::HTTP_BAD_REQUEST);
        $json->assertJson([
           'message' => 'Discount is already activated for the next day'
        ]);
    }

    /**
     *
     */
    public function testActivateDiscountNoOptions()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientPromoFavoriteAssortmentVariant $variant */
        $variant = ClientPromoFavoriteAssortmentVariant::factory()->createOne([
            'client_uuid' => $self->uuid,
            'can_be_activated_till' => now()->addWeek()
        ]);

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $json = $this->be($self)->postJson("/clients/api/profile/promo-favorite-assortment-variants/{$variant->uuid}/activate", [
            'assortment_uuid' => $assortment->uuid
        ]);
        $json->assertStatus(Response::HTTP_BAD_REQUEST);
        $json->assertJson([
           'message' => 'Options not found'
        ]);
    }

    /**
     *
     */
    public function testActivateDiscountNoLeftDays()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientPromoFavoriteAssortmentVariant $variant */
        $variant = ClientPromoFavoriteAssortmentVariant::factory()->createOne([
            'client_uuid' => $self->uuid,
            'can_be_activated_till' => now()->addDay(),
            'updated_at' => now()->subDay()->endOfDay()
        ]);

        PromoFavoriteAssortmentSetting::factory()->createOne([
            'number_of_active_days' => 1
        ]);

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $json = $this->be($self)->postJson("/clients/api/profile/promo-favorite-assortment-variants/{$variant->uuid}/activate", [
            'assortment_uuid' => $assortment->uuid
        ]);
        $json->assertStatus(Response::HTTP_BAD_REQUEST);
        $json->assertJson([
           'message' => 'No left days for discount'
        ]);
    }

    /**
     *
     */
    public function testActivateDiscountDiscountIsAlreadyActivated()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientPromoFavoriteAssortmentVariant $variant */
        $variant = ClientPromoFavoriteAssortmentVariant::factory()->createOne([
            'client_uuid' => $self->uuid,
            'can_be_activated_till' => now()->addDay(),
            'created_at' => now()->subDay()->endOfDay()
        ]);
        /** @var ClientActivePromoFavoriteAssortment $activated */
        $activated = ClientActivePromoFavoriteAssortment::factory()->createOne([
            'client_uuid' => $self->uuid,
            'active_from' => now()->subDays(2),
            'active_to' => now()->addDay(),
            'created_at' => now()->subDay()
        ]);

        PromoFavoriteAssortmentSetting::factory()->createOne([
            'number_of_active_days' => 1
        ]);

        /** @var Assortment $assortment */
        $json = $this->be($self)->postJson("/clients/api/profile/promo-favorite-assortment-variants/{$variant->uuid}/activate", [
            'assortment_uuid' => $activated->assortment_uuid
        ]);
        $json->assertStatus(Response::HTTP_BAD_REQUEST);
        $json->assertJson([
           'message' => 'Discount is already activated for that assortment'
        ]);
    }

    /**
     *
     */
    public function testActivateDiscount()
    {
        $createdAtDays = $this->faker->numberBetween(1, 3);
        $numberActiveDays = $this->faker->numberBetween(5, 10);

        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientPromoFavoriteAssortmentVariant $variant */
        $variant = ClientPromoFavoriteAssortmentVariant::factory()->createOne([
            'client_uuid' => $self->uuid,
            'can_be_activated_till' => now()->addDay(),
            'updated_at' => now()->subDays($createdAtDays)->endOfDay()
        ]);

        /** @var PromoFavoriteAssortmentSetting $setting */
        $setting = PromoFavoriteAssortmentSetting::factory()->createOne([
            'number_of_active_days' => $numberActiveDays
        ]);

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        /** @var Assortment $assortment2 */
        $assortment2 = factory(Assortment::class)->create();
        /** @var Assortment $assortment3 */
        $assortment3 = factory(Assortment::class)->create();

        // Old active
        /** @var ClientActivePromoFavoriteAssortment $activated */
        ClientActivePromoFavoriteAssortment::factory()->createOne([
            'client_uuid' => $self->uuid,
            'assortment_uuid' => $assortment2->uuid,
            'active_from' => now()->subWeek(),
            'active_to' => now()->subDay(),
            'created_at' => now()->subWeek()
        ]);

        // Activate for today
        $json = $this->be($self)->postJson("/clients/api/profile/promo-favorite-assortment-variants/{$variant->uuid}/activate", [
            'assortment_uuid' => $assortment->uuid
        ]);
        $json->assertSuccessful();

        $table = (new ClientActivePromoFavoriteAssortment())->getTable();
        $this->assertDatabaseHas($table, [
            'client_uuid' => $self->uuid,
            'assortment_uuid' => $assortment->uuid,
            'active_to' => now()->addDays($numberActiveDays - $createdAtDays)->endOfDay(),
            'discount_percent' => $setting->discount_percent,
        ]);

        // Now activate for future
        $json = $this->be($self)->postJson("/clients/api/profile/promo-favorite-assortment-variants/{$variant->uuid}/activate", [
            'assortment_uuid' => $assortment2->uuid
        ]);
        $json->assertSuccessful();
        $this->assertDatabaseHas($table, [
            'client_uuid' => $self->uuid,
            'assortment_uuid' => $assortment2->uuid,
            'active_from' => now()->addDay()->startOfDay(),
            'active_to' => now()->addDays($numberActiveDays - $createdAtDays + 1)->endOfDay(),
            'discount_percent' => $setting->discount_percent,
        ]);
        $this->assertDatabaseHas($table, [
            'client_uuid' => $self->uuid,
            'assortment_uuid' => $assortment->uuid,
            'active_to' => now()->endOfDay(),
        ]);

        // At last try to third activation. We should get an error
        $json = $this->be($self)->postJson("/clients/api/profile/promo-favorite-assortment-variants/{$variant->uuid}/activate", [
            'assortment_uuid' => $assortment3->uuid
        ]);
        $json->assertStatus(Response::HTTP_BAD_REQUEST);
        $json->assertJson([
            'message' => 'Discount is already activated for the next day'
        ]);
    }

    /**
     * @param bool $exist
     *
     * @testWith [false]
     *           [true]
     *
     * @return void
     * @throws \Throwable
     */
    public function testVariantResolver(bool $exist = false)
    {
        $numberOfActiveDays = $this->faker->numberBetween(4, 10);
        $numberOfSumDays = $this->faker->numberBetween(4, 10);
        $thresholdAmount = $this->faker->numberBetween(1000, 2000);

        PromoFavoriteAssortmentSetting::factory()->createOne([
            'threshold_amount' => $thresholdAmount,
            'number_of_sum_days' => $numberOfSumDays,
            'number_of_active_days' => $numberOfActiveDays,
        ]);

        // Create enough client
        /** @var Client $shouldBeClient */
        $shouldBeClient = factory(Client::class)->create();
        /** @var LoyaltyCard $loyaltyCard */
        $loyaltyCard = factory(LoyaltyCard::class)->create([
            'client_uuid' => $shouldBeClient->uuid
        ]);
        $sums = $thresholdAmount / 2 + 100;
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->create([
            'total' => $sums,
            'loyalty_card_uuid' => $loyaltyCard->uuid,
            'created_at' => now()->subDays($this->faker->numberBetween(0, $numberOfSumDays - 2))
        ]);
        /** @var Order $order */
        $order = factory(Order::class)->create([
            'total_price_for_products_with_discount' => $sums,
            'client_uuid' => $shouldBeClient->uuid,
            'order_status_id' => OrderStatus::ID_DONE,
            'created_at' => now()->subDays($this->faker->numberBetween(0, $numberOfSumDays - 2))
        ]);
        $shouldBeDate = $order->created_at;
        if ($receipt->created_at > $shouldBeDate) {
            $shouldBeDate = $receipt->created_at;
        }

        /** @var Client $shouldNotBeSumClient */
        $shouldNotBeSumClient = factory(Client::class)->create();
        factory(Order::class)->create([
            'total_price_for_products_with_discount' => $thresholdAmount - 1,
            'client_uuid' => $shouldNotBeSumClient->uuid,
            'order_status_id' => OrderStatus::ID_DONE,
            'created_at' => now()->subDays($this->faker->numberBetween(0, $numberOfSumDays - 1))
        ]);

        /** @var Client $shouldNotBeDatesClient */
        $shouldNotBeDatesClient = factory(Client::class)->create();
        factory(Order::class)->create([
            'total_price_for_products_with_discount' => $thresholdAmount - 1,
            'client_uuid' => $shouldNotBeDatesClient->uuid,
            'order_status_id' => OrderStatus::ID_DONE,
            'created_at' => now()->subDays($this->faker->numberBetween(0, $numberOfSumDays - 1))
        ]);
        factory(Receipt::class)->create([
            'total' => $sums,
            'loyalty_card_uuid' => $loyaltyCard->uuid,
            'created_at' => now()->subDays($this->faker->numberBetween($numberOfSumDays + 2, $numberOfSumDays + 10))
        ]);

        ClientActivePromoFavoriteAssortment::factory()->createOne([
            'client_uuid' => $shouldBeClient->uuid,
            'active_from' => now()->subDay(),
            'active_to' => now()->addDay(),
        ]);

        if ($exist) {
            ClientPromoFavoriteAssortmentVariant::factory()->createOne([
                'client_uuid' => $shouldBeClient->uuid,
                'can_be_activated_till' => now()->subDays($numberOfActiveDays),
                'updated_at' => now()->subDays($numberOfActiveDays * 2)
            ]);
        }

        $this->artisan('promo-favorite-assortment:resolve');
        $variant = new ClientPromoFavoriteAssortmentVariant();
        try {
            $this->assertDatabaseHas($variant->getTable(), [
                'client_uuid' => $shouldBeClient->uuid,
                'can_be_activated_till' => $shouldBeDate->addDays($numberOfActiveDays)->endOfDay()
            ]);
            $this->assertDatabaseMissing($variant->getTable(), [
                'client_uuid' => $shouldNotBeSumClient->uuid,
            ]);
            $this->assertDatabaseMissing($variant->getTable(), [
                'client_uuid' => $shouldNotBeDatesClient->uuid,
            ]);

            $first = ClientActivePromoFavoriteAssortment::whereClientUuid($shouldBeClient->uuid)->first();
            $this->assertEquals(now()->addDays($numberOfActiveDays)->endOfDay()->timestamp, $first->active_to->timestamp);
        } catch (\Throwable $e) {
            echo "Failing..." , PHP_EOL;
            echo "Number of active days: $numberOfActiveDays " , PHP_EOL;
            echo "Number of sum days: $numberOfSumDays " , PHP_EOL;
            echo "Threshold amount: $thresholdAmount " , PHP_EOL;
            throw $e;
        }
    }
}
