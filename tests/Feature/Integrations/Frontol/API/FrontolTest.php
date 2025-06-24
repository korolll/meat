<?php

namespace Tests\Feature\Integrations\Frontol\API;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientBonusTransaction;
use App\Models\LoyaltyCard;
use App\Models\Product;
use App\Models\PromoDescription;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Models\PromoFavoriteAssortmentSetting;
use App\Models\PromoYellowPrice;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\User;
use App\Services\Money\MoneyHelper;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCaseNotificationsFake;

class FrontolTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @testWith [false]
     *           [true]
     */
    public function testDocumentCalculateNoClient(bool $empty = false)
    {
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();

        $data = [
            'action' => 'calculate',
            'client' => ['card' => $empty ? "" : "123"],
            'businessUnit' => $store->uuid
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data);
        $json->assertSuccessful();

        $assertData = [
            'code' => 0,
            'client' => [],
        ];

        if (! $empty) {
            $assertData['cashierInformation'] = [['text' => 'Клиент не найден']];
        }
        $json->assertJson($assertData);
    }

    /**
     *
     */
    public function testDocumentCalculateAndConfirmWithBonuses()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create([
            'bonus_balance' => 10000
        ]);
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
            'price' => $this->faker->numberBetween(100, 200)
        ]);

        /** @var Assortment $assortment2 */
        $assortment2 = factory(Assortment::class)->create([
            'barcode' => '321'
        ]);
        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'assortment_uuid' => $assortment2->uuid,
            'user_uuid' => $store->uuid,
            'price' => $product->price * 2
        ]);

        $quantity1 = (int)$this->faker->randomFloat(0, 1, 10);
        $priceDiff = 10;
        $yellowDiscount = $this->faker->randomFloat(2, 20, 40);

        $discount = MoneyHelper::percentOf($yellowDiscount, $product->price - $priceDiff);
        $discountAmount = MoneyHelper::of($discount)
            ->multipliedBy($quantity1);
        $discountAmount = MoneyHelper::toFloat($discountAmount);

        $yellowPrice = MoneyHelper::of($product->price)
            ->minus($priceDiff)
            ->minus($discount);

        /** @var PromoYellowPrice $yellow */
        $yellow = factory(PromoYellowPrice::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'price' => MoneyHelper::toFloat($yellowPrice),
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'is_enabled' => true
        ]);
        $yellow->stores()->sync([$store->uuid]);

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
        $discount2 = MoneyHelper::percentOf($diverseFoodDiscount, $product2->price);
        $discountAmount2 = MoneyHelper::of($discount2)
            ->multipliedBy($quantity2);
        $discountAmount2 = MoneyHelper::toFloat($discountAmount2);

        $totalForProduct1WithoutDiscount = MoneyHelper::of($product->price - $priceDiff)
            ->multipliedBy($quantity1);
        $totalForProduct2WithoutDiscount = MoneyHelper::of($product2->price)
            ->multipliedBy($quantity2);

        $totalForProduct1 = $yellowPrice
            ->multipliedBy($quantity1);
        $totalForProduct2 = $diverseFoodPriceWithDiscount
            ->multipliedBy($quantity2);

        $total = MoneyHelper::toFloat($totalForProduct1->plus($totalForProduct2));

        $bonusPercent = 10;
        Config::set('app.order.price.bonus.max_percent_to_pay', $bonusPercent);
        $totalBonus = MoneyHelper::toBonus(MoneyHelper::percentOf($bonusPercent, $total));

        $uuid = $this->faker->uuid;
        $data = [
            'action' => 'calculate',
            'uid' => $uuid,
            'client' => ['mobilePhone' => $client->phone],
            'businessUnit' => $store->uuid,
            'positions' => [
                [
                    'id' => $assortment->article,
                    'index' => 1,
                    'price' => $product->price - $priceDiff,
                    'totalAmount' => MoneyHelper::toFloat($totalForProduct1WithoutDiscount),
                    'text' => $assortment->name,
                    'quantity' => $quantity1,
                ],
                [
                    'id' => $assortment2->article,
                    'index' => 2,
                    'price' => $product2->price,
                    'totalAmount' => MoneyHelper::toFloat($totalForProduct2WithoutDiscount),
                    'text' => $assortment2->name,
                    'quantity' => $quantity2,
                ]
            ]
        ];

        $token = Str::random();
        Config::set('app.integrations.frontol.token', $token);

        // Pre check
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data, $token);
        $json->assertSuccessful();
        $json->assertJsonFragment([
            'client' => [
                'mobilePhone' => $client->phone,
                'email' => $client->email,
                'availableAmount' => $totalBonus
            ],
        ]);

        $json->assertJsonFragment([
            'document' => [
                'positions' => [
                    [
                        'index' => 1,
                        'discountAmount' => $discountAmount
                    ],
                    [
                        'index' => 2,
                        'discountAmount' => $discountAmount2
                    ]
                ]
            ]
        ]);

        // Apply bonuses
        $data['action'] = 'payByBonus';
        $data['payments'] = [
            [
                'amount' => $total,
                'type' => 'cach',
                'mode' => 'fiscal'
            ],
            [],
            [

                'amount' => $totalBonus,
                'type' => 'bonus',
                'mode' => 'nonFiscal'
            ]
        ];
        $data['payment'] = [
            'amount' => $totalBonus,
            'type' => 'bonus',
            'mode' => 'nonFiscal'
        ];

        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data, $token);
        $json->assertSuccessful();

        // And finally, confirm
        $data['action'] = 'confirm';
        $data['number'] = 123;
        $data['dateTime'] = now()->format('Y-m-d\TH:i:s.uO');
        $data['positions'][0]['totalAmount'] = MoneyHelper::toFloat($totalForProduct1);
        $data['positions'][1]['totalAmount'] = MoneyHelper::toFloat($totalForProduct2);
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data, $token);
        $json->assertSuccessful();

        $json->assertJson(['code' => 0]);
        $this->assertDatabaseHas('receipts', [
            'uuid' => $uuid,
            'user_uuid' => $data['businessUnit'],
            'total' => $total,
            'loyalty_card_number' => $card->number,
            'loyalty_card_uuid' => $card->uuid,
            'loyalty_card_type_uuid' => $card->loyalty_card_type_uuid,
            'paid_bonus' => $totalBonus
        ]);

        $receiptFake = new ReceiptLine();
        $receiptFake->discountable()->associate($yellow);
        $this->assertDatabaseHas('receipt_lines', [
            'barcode' => $assortment->barcode,
            'product_uuid' => $product->uuid,
            'assortment_uuid' => $assortment->uuid,
            'total' => $data['positions'][0]['totalAmount'],
            'quantity' => $data['positions'][0]['quantity'],
            'discountable_type' => $receiptFake->discountable_type,
            'discountable_uuid' => $receiptFake->discountable_uuid,
            'price_with_discount' => MoneyHelper::toFloat($yellowPrice),
            'discount' => $discount,
        ]);

        $receiptFake->discountable()->associate($diverseFoodDiscountModel);
        $this->assertDatabaseHas('receipt_lines', [
            'barcode' => $assortment2->barcode,
            'product_uuid' => $product2->uuid,
            'assortment_uuid' => $assortment2->uuid,
            'total' => $data['positions'][1]['totalAmount'],
            'quantity' => $data['positions'][1]['quantity'],
            'discountable_type' => $receiptFake->discountable_type,
            'discountable_uuid' => $receiptFake->discountable_uuid,
            'price_with_discount' => MoneyHelper::toFloat($diverseFoodPriceWithDiscount),
            'discount' => $diverseFoodDiscountValue,
        ]);

        $this->assertDatabaseHas('client_bonus_transactions', [
            'client_uuid' => $client->uuid,
            'quantity_old' => $client->bonus_balance,
            'quantity_delta' => -$totalBonus,
            'quantity_new' => $client->bonus_balance - $totalBonus,
            'related_reference_type' => Receipt::MORPH_TYPE_ALIAS,
            'related_reference_id' => $uuid,
            'reason' => ClientBonusTransaction::REASON_PURCHASE_PAID,
        ]);
    }

    /**
     *
     */
    public function testDocumentCalculateAndConfirmWithoutBonuses()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create([
            'bonus_balance' => 10000
        ]);
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var LoyaltyCard $card */
        $card = factory(LoyaltyCard::class)->create();
        $card->client()->associate($client)->save();

        $bonusPercent1 = $this->faker->randomFloat(0, 10, 20);
        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'barcode' => '123',
            'bonus_percent' => $bonusPercent1
        ]);
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $store->uuid,
            'price' => $this->faker->numberBetween(100, 200)
        ]);

        $bonusPercent2 = $this->faker->randomFloat(0, 10, 20);
        /** @var Assortment $assortment2 */
        $assortment2 = factory(Assortment::class)->create([
            'barcode' => '321',
            'bonus_percent' => $bonusPercent2
        ]);
        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'assortment_uuid' => $assortment2->uuid,
            'user_uuid' => $store->uuid,
            'price' => $product->price * 2
        ]);


        /** @var Assortment $assortment3 */
        $assortment3 = factory(Assortment::class)->create([
            'barcode' => '4444',
            'bonus_percent' => $bonusPercent2
        ]);
        /** @var Product $product3 */
        $product3 = factory(Product::class)->create([
            'assortment_uuid' => $assortment3->uuid,
            'user_uuid' => $store->uuid,
            'price' => $product->price * 3
        ]);

        $quantity1 = (int)$this->faker->randomFloat(0, 1, 10);
        $quantity22 = (int)$this->faker->randomFloat(0, 1, 10);
        $yellowDiscount = $this->faker->randomFloat(2, 20, 40);
        $yellowDiscount22 = $this->faker->randomFloat(2, 40, 60);

        $noAssortmentPrice = $this->faker->randomFloat(2, 100, 400);

        $discount = MoneyHelper::percentOf($yellowDiscount, $product->price);
        $discountAmount = MoneyHelper::of($discount)
            ->multipliedBy($quantity1);
        $discountAmount = MoneyHelper::toFloat($discountAmount);

        $discount22 = MoneyHelper::percentOf($yellowDiscount22, $product->price);
        $discountAmount22 = MoneyHelper::of($discount22)
            ->multipliedBy($quantity22);
        $discountAmount22 = MoneyHelper::toFloat($discountAmount22);

        $yellowPrice = MoneyHelper::of($product->price)
            ->minus($discount);
        $yellowPrice2 = MoneyHelper::of($product2->price)
            ->minus($discount22);

        /** @var PromoYellowPrice $yellow */
        $yellow = factory(PromoYellowPrice::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'price' => MoneyHelper::toFloat($yellowPrice),
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'is_enabled' => true
        ]);
        /** @var PromoYellowPrice $yellow2 */
        $yellow2 = factory(PromoYellowPrice::class)->create([
            'assortment_uuid' => $assortment2->uuid,
            'price' => MoneyHelper::toFloat($yellowPrice2),
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'is_enabled' => true
        ]);
        $yellow->stores()->sync([$store->uuid]);
        $yellow2->stores()->sync([$store->uuid]);
        // Enable first order discount (but it must not be applied)
        Config::set('app.order.price.first_order_discount_resolver_config.discount_percent', 10);

        $quantity2 = (int)$this->faker->randomFloat(0, 1, 10);
        $product2PriceDiff = 10;
        $product2Price = MoneyHelper::of($product2->price)->minus($product2PriceDiff);

        $quantity3 = (int)$this->faker->randomFloat(0, 1, 10);

        $totalForProduct1WithoutDiscount = MoneyHelper::of($product->price)
            ->multipliedBy($quantity1);
        $totalForProduct22WithoutDiscount = MoneyHelper::of($product2->price)
            ->multipliedBy($quantity22);

        $totalForProduct1 = $yellowPrice
            ->multipliedBy($quantity1);
        $totalForProduct2 = $product2Price
            ->multipliedBy($quantity2);
        $totalForProduct22 = $yellowPrice2
            ->multipliedBy($quantity22);
        $totalForProduct3 = MoneyHelper::of($product->price * 3)
            ->multipliedBy($quantity3);

        $totalBonusChangeForProduct1 = $totalForProduct1
            ->multipliedBy($bonusPercent1)
            ->dividedBy(100);
        $totalBonusChangeForProduct1 = MoneyHelper::toBonus($totalBonusChangeForProduct1);

        $totalBonusChangeForProduct2 = $totalForProduct2
            ->multipliedBy($bonusPercent2)
            ->dividedBy(100);
        $totalBonusChangeForProduct2 = MoneyHelper::toBonus($totalBonusChangeForProduct2);

        $totalBonusChangeForProduct22 = $totalForProduct22
            ->multipliedBy($bonusPercent2)
            ->dividedBy(100);
        $totalBonusChangeForProduct22 = MoneyHelper::toBonus($totalBonusChangeForProduct22);

        $totalBonusChangeForProduct3 = $totalForProduct3
            ->multipliedBy($bonusPercent2)
            ->dividedBy(100);
        $totalBonusChangeForProduct3 = MoneyHelper::toBonus($totalBonusChangeForProduct3);

        $total = MoneyHelper::toFloat(
            $totalForProduct1
                ->plus($totalForProduct2)
                ->plus($totalForProduct22)
                ->plus($totalForProduct3)
                ->plus($noAssortmentPrice)
        );

        $bonusPercent = 10;
        Config::set('app.order.price.bonus.max_percent_to_pay', $bonusPercent);
        $totalBonus = MoneyHelper::toBonus(MoneyHelper::percentOf($bonusPercent, $total - $noAssortmentPrice));

        $uuid = $this->faker->uuid;
        $data = [
            'action' => 'calculate',
            'uid' => $uuid,
            'client' => ['mobilePhone' => $client->phone],
            'businessUnit' => $store->uuid,
            'positions' => [
                // Yellow discount
                [
                    'id' => $assortment->article,
                    'index' => 1,
                    'price' => $product->price,
                    'totalAmount' => MoneyHelper::toFloat($totalForProduct1WithoutDiscount),
                    'text' => $assortment->name,
                    'quantity' => $quantity1,
                ],
                // Green discount
                [
                    'id' => $assortment2->article,
                    'index' => 2,
                    'price' => $product2->price,
                    'totalAmount' => MoneyHelper::toFloat($totalForProduct2),
                    'text' => $assortment2->name,
                    'quantity' => $quantity2,
                ],
                // Yellow discount + same product as above
                [
                    'id' => $assortment2->article,
                    'index' => 3,
                    'price' => $product2->price,
                    'totalAmount' => MoneyHelper::toFloat($totalForProduct22WithoutDiscount),
                    'text' => $assortment2->name,
                    'quantity' => $quantity22,
                ],
                // No discount
                [
                    'id' => $assortment3->article,
                    'index' => 4,
                    'price' => $product3->price,
                    'totalAmount' => MoneyHelper::toFloat($totalForProduct3),
                    'text' => $assortment3->name,
                    'quantity' => $quantity3,
                ],
                // No assortment
                [
                    'id' => $this->faker->uuid,
                    'index' => 5,
                    'price' => $noAssortmentPrice,
                    'totalAmount' => $noAssortmentPrice,
                    'text' => 'No assortment product',
                    'quantity' => 1,
                ]
            ]
        ];

        $token = Str::random();
        Config::set('app.integrations.frontol.token', $token);

        // Pre check
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data, $token);
        $json->assertSuccessful();
        $json->assertJsonFragment([
            'client' => [
                'mobilePhone' => $client->phone,
                'email' => $client->email,
                'availableAmount' => $totalBonus
            ],
        ]);

        $json->assertJsonFragment([
            'document' => [
                'positions' => [
                    [
                        'index' => 1,
                        'discountAmount' => $discountAmount
                    ],
                    [
                        'index' => 3,
                        'discountAmount' => $discountAmount22
                    ],
                ]
            ]
        ]);

        // Confirm
        $data['action'] = 'confirm';
        $data['number'] = 123;
        $data['dateTime'] = now()->format('Y-m-d\TH:i:s.uO');
        $data['positions'][0]['totalAmount'] = MoneyHelper::toFloat($totalForProduct1);
        $data['positions'][1]['totalAmount'] = MoneyHelper::toFloat($totalForProduct2);
        $data['positions'][2]['totalAmount'] = MoneyHelper::toFloat($totalForProduct22);
        $data['positions'][3]['totalAmount'] = MoneyHelper::toFloat($totalForProduct3);

        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data, $token);
        $json->assertSuccessful();

        $json->assertJson(['code' => 0]);
        $this->assertDatabaseHas('receipts', [
            'uuid' => $uuid,
            'user_uuid' => $data['businessUnit'],
            'total' => $total,
            'loyalty_card_number' => $card->number,
            'loyalty_card_uuid' => $card->uuid,
            'loyalty_card_type_uuid' => $card->loyalty_card_type_uuid,
        ]);

        $receiptFake = new ReceiptLine();
        $receiptFake->discountable()->associate($yellow);
        // First: Yellow discount
        $this->assertDatabaseHas('receipt_lines', [
            'barcode' => $assortment->barcode,
            'product_uuid' => $product->uuid,
            'assortment_uuid' => $assortment->uuid,
            'total' => $data['positions'][0]['totalAmount'],
            'quantity' => $data['positions'][0]['quantity'],
            'discountable_type' => $receiptFake->discountable_type,
            'discountable_uuid' => $receiptFake->discountable_uuid,
            'price_with_discount' => MoneyHelper::toFloat($yellowPrice),
            'paid_bonus' => 0,
            'total_bonus' => $totalBonusChangeForProduct1,
            'discount' => $discount,
        ]);

        // Second: Green discount
        $this->assertDatabaseHas('receipt_lines', [
            'barcode' => $assortment2->barcode,
            'product_uuid' => $product2->uuid,
            'assortment_uuid' => $assortment2->uuid,
            'total' => $data['positions'][1]['totalAmount'],
            'quantity' => $data['positions'][1]['quantity'],
            'discountable_uuid' => PromoDescription::VIRTUAL_FRONTOL_DISCOUNT_UUID,
            'discountable_type' => PromoDescription::class,
            'price_with_discount' => MoneyHelper::toFloat($product2Price),
            'paid_bonus' => 0,
            'total_bonus' => $totalBonusChangeForProduct2,
            'discount' => $product2PriceDiff,
        ]);

        $receiptFake2 = new ReceiptLine();
        $receiptFake2->discountable()->associate($yellow2);
        // Third: Yellow discount
        $this->assertDatabaseHas('receipt_lines', [
            'barcode' => $assortment2->barcode,
            'product_uuid' => $product2->uuid,
            'assortment_uuid' => $assortment2->uuid,
            'total' => $data['positions'][2]['totalAmount'],
            'quantity' => $data['positions'][2]['quantity'],
            'discountable_type' => $receiptFake2->discountable_type,
            'discountable_uuid' => $receiptFake2->discountable_uuid,
            'price_with_discount' => MoneyHelper::toFloat($yellowPrice2),
            'paid_bonus' => 0,
            'total_bonus' => $totalBonusChangeForProduct22,
            'discount' => $discount22,
        ]);

        // Fourth: No discount
        $this->assertDatabaseHas('receipt_lines', [
            'barcode' => $assortment3->barcode,
            'product_uuid' => $product3->uuid,
            'assortment_uuid' => $assortment3->uuid,
            'total' => $data['positions'][3]['totalAmount'],
            'quantity' => $data['positions'][3]['quantity'],
            'discountable_uuid' => null,
            'discountable_type' => null,
            'price_with_discount' => $product3->price,
            'paid_bonus' => 0,
            'total_bonus' => $totalBonusChangeForProduct3,
            'discount' => 0,
        ]);

        // Fifth: No assortment
        $this->assertDatabaseHas('receipt_lines', [
            'barcode' => '',
            'product_uuid' => null,
            'assortment_uuid' => null,
            'total' => $data['positions'][4]['totalAmount'],
            'quantity' => 1,
            'discountable_uuid' => null,
            'discountable_type' => null,
            'price_with_discount' => $data['positions'][4]['totalAmount'],
            'paid_bonus' => null,
            'total_bonus' => null,
            'discount' => 0,
        ]);

        $this->assertDatabaseHas('client_bonus_transactions', [
            'client_uuid' => $client->uuid,
            'quantity_old' => $client->bonus_balance,
            'quantity_delta' =>
                $totalBonusChangeForProduct1
                + $totalBonusChangeForProduct2
                + $totalBonusChangeForProduct22
                + $totalBonusChangeForProduct3,
            'quantity_new' =>
                $client->bonus_balance
                + $totalBonusChangeForProduct1
                + $totalBonusChangeForProduct2
                + $totalBonusChangeForProduct22
                + $totalBonusChangeForProduct3,
            'related_reference_type' => Receipt::MORPH_TYPE_ALIAS,
            'related_reference_id' => $uuid,
            'reason' => ClientBonusTransaction::REASON_PURCHASE_DONE,
        ]);
    }

    /**
     *
     */
    public function testDocumentPayByBonus()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();
        $data = [
            'action' => 'payByBonus',
            'client' => ['mobilePhone' => $client->phone],
            'document' => ['positions' => []]
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment(['client' => [
            'mobilePhone' => $client->phone,
            'email' => $client->email,
        ]]);
    }

    /**
     *
     */
    public function testDocumentCancelBonusPayment()
    {
        /** @var Client $client */
        $client = factory(Client::class)->make();
        $data = [
            'action' => 'cancelBonusPayment',
            'client' => ['mobilePhone' => $client->phone]
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data);
        $json->assertSuccessful();
        $json->assertJson(['code' => 0]);
    }

    /**
     *
     */
    public function testDocumentConfirmNoStoreData()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();
        $data = [
            'action' => 'confirm',
            'client' => ['mobilePhone' => $client->phone],
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data);
        $json->assertSuccessful();
        $json->assertJson(['code' => 0]);
    }

    /**
     *
     */
    public function testDocumentConfirmNoStore()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();
        $data = [
            'action' => 'confirm',
            'client' => ['mobilePhone' => $client->phone],
            'businessUnit' => $client->uuid
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data);
        $json->assertSuccessful();
        $json->assertJson(['code' => 0]);
    }

    /**
     *
     */
    public function testDocumentConfirmNoClient()
    {
        /** @var Client $client */
        $client = factory(Client::class)->make();
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();

        $data = [
            'action' => 'confirm',
            'client' => ['mobilePhone' => $client->phone],
            'businessUnit' => $store->uuid
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data);
        $json->assertSuccessful();
        $json->assertJson(['code' => 0]);
    }

    /**
     *
     */
    public function testDocumentConfirmNoLoyaltyCards()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();

        $data = [
            'action' => 'confirm',
            'client' => ['mobilePhone' => $client->phone],
            'businessUnit' => $store->uuid
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data);
        $json->assertSuccessful();
        $json->assertJson(['code' => 0]);
    }

    /**
     *
     */
    public function testDocumentConfirmWithoutClient()
    {
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create([
            'barcode' => '123'
        ]);
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $store->uuid
        ]);

        $data = [
            'action' => 'confirm',
            'client' => [],
            'businessUnit' => $store->uuid,
            'type' => 'receipt',
            'number' => $this->faker->randomDigit,
            'uid' => $this->faker->uuid,
            'dateTime' => $this->faker->dateTime->format('Y-m-d\TH:i:s.uO'),
            'positions' => [
                [
                    'id' => $assortment->article,
                    'price' => $this->faker->randomNumber(),
                    'totalAmount' => $this->faker->randomNumber(),
                    'quantity' => $this->faker->randomNumber(),
                ]
            ]
        ];

        $position = $data['positions'][0];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data);
        $json->assertSuccessful();
        $json->assertJson(['code' => 0]);
        $this->assertDatabaseHas('receipts', [
            'uuid' => $data['uid'],
            'user_uuid' => $data['businessUnit'],
            'total' => $position['totalAmount'],
            'loyalty_card_number' => '',
            'loyalty_card_uuid' => null,
            'loyalty_card_type_uuid' => null,
        ]);

        $this->assertDatabaseHas('receipt_lines', [
            'barcode' => $assortment->barcode,
            'product_uuid' => $product->uuid,
            'assortment_uuid' => $assortment->uuid,
            'price_with_discount' => $position['price'],
            'total' => $position['totalAmount'],
            'quantity' => $position['quantity'],
        ]);
    }

    /**
     *
     */
    public function testDocumentConfirmRefund()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();
        /** @var LoyaltyCard $card */
        $card = factory(LoyaltyCard::class)->create();
        $card->client()->associate($client)->save();

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $store->uuid
        ]);

        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->create();
        $data = [
            'action' => 'confirm',
            'referenceUid' => $receipt->uuid,
            'client' => ['mobilePhone' => $client->phone],
            'businessUnit' => $store->uuid,
            'type' => 'refundReceipt',
            'number' => $this->faker->randomDigit,
            'uid' => $this->faker->uuid,
            'dateTime' => $this->faker->dateTime->format('Y-m-d\TH:i:s.uO'),
            'positions' => [
                [
                    'id' => $assortment->article,
                    'price' => $this->faker->randomNumber(),
                    'totalAmount' => $this->faker->randomNumber(),
                    'quantity' => $this->faker->randomNumber(),
                ]
            ]
        ];

        $position = $data['positions'][0];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/document', $data);
        $json->assertSuccessful();
        $json->assertJson(['code' => 0]);
        $this->assertDatabaseHas('receipts', [
            'uuid' => $data['uid'],
            'user_uuid' => $data['businessUnit'],
            'total' => $position['totalAmount'],
            'loyalty_card_number' => $card->number,
            'loyalty_card_uuid' => $card->uuid,
            'loyalty_card_type_uuid' => $card->loyalty_card_type_uuid,
            'refund_by_receipt_uuid' => $data['referenceUid'],
        ]);

        $this->assertDatabaseHas('receipt_lines', [
            'product_uuid' => $product->uuid,
            'assortment_uuid' => $assortment->uuid,
            'price_with_discount' => $position['price'],
            'total' => -$position['totalAmount'],
            'quantity' => $position['quantity'],
        ]);
    }

    /**
     *
     */
    public function testClientRegistration()
    {
        $token = Str::random();
        Config::set('app.integrations.frontol.token', $token);
        /** @var Client $client */
        $client = factory(Client::class)->make();

        // Describe
        $data = [
            'action' => 'describe',
            'client' => [
                'mobilePhone' => $client->phone
            ]
        ];

        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/extra/client', $data, $token);
        $json->assertSuccessful();
        $json->assertJson([
            'form' => [
                'title' => ['text' => 'Анкета клиента'],
                'elements' => [[
                    'type' => 'inputLine',
                    'name' => 'card',
                    'text' => 'Карта клиента: ',
                    'regExp' => '^\d{3,20}$',
                ]]
            ]
        ]);

        /** @var LoyaltyCard $card */
        $card = factory(LoyaltyCard::class)->create();

        // Check
        $data = [
            'action' => 'check',
            'client' => [
                'mobilePhone' => $client->phone
            ],
            'values' => [
                [
                    'name' => 'card',
                    'value' => $card->number
                ]
            ]
        ];

        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/extra/client', $data, $token);
        $json->assertSuccessful();
        $code = $json->json('client.validationCode');
        $this->assertNotNull($code);

        $this->assertDatabaseHas('clients', [
            'phone' => $client->phone
        ]);
        $this->assertDatabaseHas('client_authentication_codes', [
            'code' => $code
        ]);

        // Register
        $data['action'] = 'execute';
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/extra/client', $data, $token);
        $json->assertSuccessful();
        $json->assertJson([
            'cashierInformation' => [['text' => 'Клиент зарегестрирован']]
        ]);

        $card->refresh();
        $this->assertNotNull($card->client()->get());
    }

    /**
     *
     */
    public function testClientExtraInvalidData()
    {
        /** @var Client $client */
        $client = factory(Client::class)->make();
        $data = [
            'action' => 'check',
            'client' => ['mobilePhone' => $client->phone]
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/extra/client', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment(['error' => 'Ошибка валидации: Поле card обязательно для заполнения.']);
    }

    /**
     *
     */
    public function testClientExtraBadIdentifier()
    {
        $data = [
            'client' => ['card' => 123]
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/extra/client', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment(['error' => 'Укажите телефон как идентификатор клиента']);
    }

    /**
     *
     */
    public function testClientExtraBadAction()
    {
        $data = [
            'client' => ['mobilePhone' => '+79998887766']
        ];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/extra/client', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment(['error' => 'Некорректный запрос']);
    }

    /**
     *
     */
    public function testClient()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();
        $data = [
            'client' => [
                'mobilePhone' => $client->phone
            ]
        ];

        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/client', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment([
            'printingInformation' => [['text' => 'Клиент ' . $client->name]]
        ]);
    }

    /**
     *
     */
    public function testClientNotExist()
    {
        /** @var Client $client */
        $client = factory(Client::class)->make();
        $data = [
            'client' => [
                'mobilePhone' => $client->phone
            ]
        ];

        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/client', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment([
            'cashierInformation' => [['text' => 'Клиент не найдет']]
        ]);
    }

    /**
     *
     */
    public function testClientInvalidClient()
    {
        // invalid data
        $data = ['client' => [1]];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/client', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment([
            'error' => 'Некорректно указаны данные клиента'
        ]);
    }

    /**
     *
     */
    public function testClientInvalidData()
    {
        $data = [];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/client', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment([
            'error' => 'Данные клиента не указаны'
        ]);
    }

    /**
     *
     */
    public function testClientInvalidPhone()
    {
        $data = ['client' => ['mobilePhone' => 1]];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/client', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment([
            'error' => 'Некорректно указан телефон клиента'
        ]);
    }

    /**
     *
     */
    public function testClientInvalidCard()
    {
        $data = ['client' => ['card' => 1]];
        $json = $this->postWithToken('/integrations/frontol/api/loyalty-system/client', $data);
        $json->assertSuccessful();
        $json->assertJsonFragment([
            'error' => 'Некорректно указана карта клиента'
        ]);
    }

    /**
     * @param string      $url
     * @param array       $data
     * @param string|null $token
     *
     * @return \Illuminate\Testing\TestResponse
     */
    protected function postWithToken(string $url, array $data = [], ?string $token = null): TestResponse
    {
        if (! $token) {
            $token = Str::random();
            Config::set('app.integrations.frontol.token', $token);
        }

        $body = json_encode($data);
        $hash = md5($token . $body);
        return $this->postJson($url, $data, [
            'Authorization' => 'FrontolAuth ' . $hash
        ]);
    }
}
