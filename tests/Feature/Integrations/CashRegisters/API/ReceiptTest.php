<?php

namespace Tests\Feature\Integrations\CashRegisters\API;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\LoyaltyCard;
use App\Models\Product;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Models\PromoYellowPrice;
use App\Models\Receipt;
use App\Models\User;
use App\Services\Money\MoneyHelper;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCaseNotificationsFake;

class ReceiptTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Чтобы отключить проверку токена
        Config::set('app.integrations.cash-registers.token', null);
    }

    /**
     *
     */
    public function testCalculate()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var LoyaltyCard $card */
        $card = factory(LoyaltyCard::class)->create();
        $card->client()->associate($client)->save();

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'barcode' => '123'
        ]);
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $store->uuid,
            'price' => $this->faker->numberBetween(100, 1000)
        ]);

        /** @var Assortment $assortment2 */
        $assortment2 = factory(Assortment::class)->create([
            'barcode' => '321',
            'bonus_percent' => 50
        ]);
        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'assortment_uuid' => $assortment2->uuid,
            'user_uuid' => $store->uuid,
            'price' => $this->faker->numberBetween(100, 1000)
        ]);

        $quantity1 = (int)$this->faker->randomFloat(0, 1, 10);
        $priceDiff = 10;
        $yellowDiscount = $this->faker->randomFloat(2, 20, 40);
        $discount = MoneyHelper::percentOf($yellowDiscount, $product->price - $priceDiff);

        $yellowPrice = MoneyHelper::of($product->price)
            ->minus($priceDiff)
            ->minus($discount);
        $yellowPrice = MoneyHelper::toFloat($yellowPrice);
        /** @var PromoYellowPrice $yellow */
        $yellow = factory(PromoYellowPrice::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'price' => $yellowPrice,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'is_enabled' => true
        ]);
        $yellow->stores()->sync([$store->uuid]);

        $uuid = $this->faker->uuid;

        // This discount should be smaller than $yellowDiscount
        $diverseFoodDiscount = $this->faker->randomFloat(2, 5, 15);
        /** @var PromoDiverseFoodClientDiscount $diverseFoodDiscountModel */
        $diverseFoodDiscountModel = PromoDiverseFoodClientDiscount::factory()->createOne([
            'client_uuid' => $client->uuid,
            'discount_percent' => $diverseFoodDiscount,
            'start_at' => now()->startOfMonth(),
            'end_at' => now()->endOfMonth(),
        ]);

        $diverseFoodDiscountValue = MoneyHelper::percentOf($diverseFoodDiscount, $product2->price);
        $diverseFoodPriceWithDiscount = MoneyHelper::of($product2->price)
            ->minus($diverseFoodDiscountValue);

        $quantity2 = (int)$this->faker->randomFloat(0, 1, 10);
        $totalBonus = MoneyHelper::of($diverseFoodPriceWithDiscount->getAmount())
            ->multipliedBy($quantity2)
            ->multipliedBy(0.5); // 50%

        Cache::shouldReceive('set')
            ->once()
            ->with('cash_reg_receipt_discount:' . $uuid, [
                $assortment->uuid => [
                    'discountable_type' => PromoYellowPrice::class,
                    'discountable_uuid' => $yellow->uuid,
                    'price_with_discount' => $yellowPrice,
                    'discount' => $discount,
                ],
                $assortment2->uuid => [
                    'discountable_type' => PromoDiverseFoodClientDiscount::class,
                    'discountable_uuid' => $diverseFoodDiscountModel->uuid,
                    'price_with_discount' => MoneyHelper::toFloat($diverseFoodPriceWithDiscount),
                    'discount' => $diverseFoodDiscountValue,
                    'total_bonus' => MoneyHelper::toBonus($totalBonus)
                ]
            ], 7200);

        $discount2 = MoneyHelper::percentOf($diverseFoodDiscount, $product2->price);
        $price2 = MoneyHelper::of($product2->price)->minus($discount2);
        $price2 = MoneyHelper::toFloat($price2);

        $data = [
            'uuid' => $uuid,
            'store_uuid' => $store->uuid,
            'loyalty_card_uuid' => $card->uuid,
            'total' => 123,
            'items' => [
                [
                    'name' => $assortment->name,
                    'number' => $assortment->article,
                    'price' => $product->price - $priceDiff,
                    'sum' => ($product->price - $priceDiff) * $quantity1,
                    'count' => $quantity1,
                ],
                [
                    'name' => $assortment2->name,
                    'number' => $assortment2->article,
                    'price' => $product2->price,
                    'sum' => $product2->price * $quantity2,
                    'count' => $quantity2,
                ]
            ]
        ];
        $json = $this->postJson('/integrations/cash-registers/api/receipts/calculate-discount', $data);
        $json->assertSuccessful();

        $totalNew1 = MoneyHelper::of($yellowPrice)
            ->multipliedBy($quantity1);
        $totalNew2 = MoneyHelper::of($price2)
            ->multipliedBy($quantity2);
        $totalNew = $totalNew1->plus($totalNew2);
        $totalNew = MoneyHelper::toFloat($totalNew);

        $json->assertJson([
            'items' => [
                [
                    'number' => $assortment->article,
                    'price' => $yellowPrice
                ],
                [
                    'number' => $assortment2->article,
                    'price' => $price2
                ]
            ],
            'total' => 123,
            'total_with_discount' => $totalNew
        ]);
    }

    /**
     *
     */
    public function testOrderStore()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var LoyaltyCard $card */
        $card = factory(LoyaltyCard::class)->create();
        $card->client()->associate($client)->save();

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'barcode' => '123'
        ]);
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $store->uuid
        ]);

        /** @var Receipt $refundReceipt */
        $refundReceipt = factory(Receipt::class)->create([
            'user_uuid' => $store->uuid
        ]);

        $price = $this->faker->randomFloat(2, 100, 200);
        $discount = $this->faker->randomFloat(2, 5, 20);

        $discountModel = $this->faker->name;
        $discountUuid = $this->faker->uuid;
        Cache::shouldReceive('get')
            ->with('cash_reg_receipt_discount:' . $store->uuid, [])
            ->andReturn([
                $assortment->uuid => [
                    'discountable_type' => $discountModel,
                    'discountable_uuid' => $discountUuid,
                    'price_with_discount' => $price,
                    'discount' => $discount,
                ]
            ]);
        Cache::shouldReceive('forget')
            ->with('cash_reg_receipt_discount:' . $store->uuid);

        $quantity = $this->faker->numberBetween(1, 10);
        $data = [
            'uuid' => $store->uuid,
            'refund_by_receipt_uuid' => $refundReceipt->uuid,
            'store_uuid' => $store->uuid,
            'loyalty_card_uuid' => $card->uuid,
            'receipt_id' => $this->faker->randomDigit,
            'receipt_package_id' => $this->faker->randomDigit,
            'total' => ($product->price) * $quantity,
            'items' => [
                [
                    'name' => $assortment->name,
                    'number' => $assortment->article,
                    'price' => $product->price,
                    'sum' => ($product->price) * $quantity,
                    'count' => $quantity,
                ],
            ]
        ];

        $json = $this->postJson('/integrations/cash-registers/api/receipts', $data);
        $json->assertSuccessful();
        $this->assertDatabaseHas('receipts', [
            'uuid' => $data['uuid'],
            'refund_by_receipt_uuid' => $data['refund_by_receipt_uuid'],
            'id' => $data['receipt_id'],
            'receipt_package_id' => $data['receipt_package_id'],
            'user_uuid' => $store->uuid,
            'total' => $data['total'],
            'loyalty_card_number' => $card->number,
            'loyalty_card_uuid' => $card->uuid,
            'loyalty_card_type_uuid' => $card->loyalty_card_type_uuid,
        ]);

        $this->assertDatabaseHas('receipt_lines', [
            'product_uuid' => $product->uuid,
            'assortment_uuid' => $assortment->uuid,
            'total' => $data['items'][0]['sum'],
            'quantity' => $data['items'][0]['count'],
            'discountable_type' => $discountModel,
            'discountable_uuid' => $discountUuid,
            'price_with_discount' => $price,
            'discount' => $discount,
        ]);
    }
}
