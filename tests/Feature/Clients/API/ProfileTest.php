<?php

namespace Tests\Feature\Clients\API;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientBonusTransaction;
use App\Models\LoyaltyCard;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\RatingScore;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCaseNotificationsFake;

class ProfileTest extends TestCaseNotificationsFake
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
    public function show()
    {
        $self = factory(Client::class)->create();
        $json = $this->be($self)->getJson('/clients/api/profile');

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $self->uuid,
            ],
        ]);
    }

    /**
     * @test
     * @testWith [false]
     *           [true]
     */
    public function update(bool $useSamePhone)
    {
        /** @var Client $clientOld */
        $clientOld = factory(Client::class)->create();
        /** @var Client $clientNew */
        $clientNew = factory(Client::class)
            ->state('with-selected-store')
            ->make();

        if ($useSamePhone) {
            /** @var Client $clientOldWithSamePhone */
            $clientOldWithSamePhone = factory(Client::class)->create([
                'phone' => $clientOld->phone
            ]);
            $clientOldWithSamePhone->delete();
            $clientNew->phone = $clientOld->phone;
        }

        Config::set('app.clients.bonuses_for_filled_profile', 50);
        $self = $clientOld;
        $json = $this->be($self)->putJson('/clients/api/profile', $clientNew->only([
            'phone',
            'name',
            'sex',
            'email',
            'birth_date',
            'is_agree_with_diverse_food_promo',
            'consent_to_service_newsletter',
            'consent_to_receive_promotional_mailings',
            'selected_store_user_uuid'
        ]));

        $data = [
            'uuid' => $clientOld->uuid,
            'phone' => $clientNew->phone,
            'name' => $clientNew->name,
            'sex' => $clientNew->sex,
            'email' => $clientNew->email,
            'birth_date' => $clientNew->birth_date,
            'consent_to_service_newsletter' => $clientNew->consent_to_service_newsletter,
            'consent_to_receive_promotional_mailings' => $clientNew->consent_to_receive_promotional_mailings,
            'is_agree_with_diverse_food_promo' => $clientNew->is_agree_with_diverse_food_promo,
            'selected_store_user_uuid' => $clientNew->selected_store_user_uuid,
            'selected_store_address' => $clientNew->selectedStore->address,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('clients', Arr::except($data, ['selected_store_address']));
        $this->assertDatabaseHas('client_bonus_transactions', [
            'client_uuid' => $clientOld->uuid,
            'quantity_delta' => 50,
            'reason' => ClientBonusTransaction::REASON_PROFILE_FILLED
        ]);
    }

    /**
     *
     */
    public function testPurchasesSum()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var LoyaltyCard $card */
        $card = factory(LoyaltyCard::class)->create([
            'client_uuid' => $self->uuid
        ]);

        $s1 = $this->faker->numberBetween(100, 1000);
        $s2 = $this->faker->numberBetween(100, 1000);

        factory(Order::class)->create([
            'client_uuid' => $self->uuid,
            'total_price_for_products_with_discount' => $s1,
            'order_status_id' => OrderStatus::ID_DONE
        ]);

        factory(Receipt::class)->create([
            'loyalty_card_uuid' => $card->uuid,
            'total' => $s2,
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/purchases-sum?days=10');
        $json->assertSuccessful()->assertJson([
            'data' => $s1 + $s2,
        ]);
    }

    /**
     *
     */
    public function testPurchasesMonth()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var LoyaltyCard $card */
        $card = factory(LoyaltyCard::class)->create([
            'client_uuid' => $self->uuid
        ]);

        $s1 = $this->faker->numberBetween(100, 1000);
        $s2 = $this->faker->numberBetween(100, 1000);

        /** @var Order $order */
        $order = factory(Order::class)->create([
            'client_uuid' => $self->uuid,
            'total_price_for_products_with_discount' => $s1,
            'order_status_id' => OrderStatus::ID_DONE
        ]);
        /** @var Order $order2 */
        $order2 = factory(Order::class)->create([
            'client_uuid' => $self->uuid,
            'order_status_id' => OrderStatus::ID_DONE
        ]);

        $product = factory(Product::class)->create();

        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create([
            'order_uuid' => $order->uuid,
            'product_uuid' => $product,
        ]);
        /** @var OrderProduct $orderProduct2 */
        $orderProduct2 = factory(OrderProduct::class)->create([
            'order_uuid' => $order2->uuid,
            'product_uuid' => $product,
        ]);

        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->create([
            'loyalty_card_uuid' => $card->uuid,
            'total' => $s2,
        ]);
        /** @var ReceiptLine $receiptLine */
        $receiptLine = factory(ReceiptLine::class)->create([
            'receipt_uuid' => $receipt->uuid,
        ]);

        $comment1 = $this->faker->word;
        $comment2 = $this->faker->word;

        factory(RatingScore::class)->create([
            'rated_reference_type' => Assortment::MORPH_TYPE_ALIAS,
            'rated_reference_id' => $orderProduct->product->assortment_uuid,
            'rated_by_reference_type' => Client::MORPH_TYPE_ALIAS,
            'rated_by_reference_id' => $self->uuid,
            'rated_through_reference_type' => OrderProduct::MORPH_TYPE_ALIAS,
            'rated_through_reference_id' => $orderProduct->uuid,
            'additional_attributes->comment' => $comment1
        ]);
        factory(RatingScore::class)->create([
            'rated_reference_type' => Assortment::MORPH_TYPE_ALIAS,
            'rated_reference_id' => $orderProduct2->product->assortment_uuid,
            'rated_by_reference_type' => Client::MORPH_TYPE_ALIAS,
            'rated_by_reference_id' => $self->uuid,
            'rated_through_reference_type' => OrderProduct::MORPH_TYPE_ALIAS,
            'rated_through_reference_id' => $orderProduct2->uuid,
        ]);

        factory(RatingScore::class)->create([
            'rated_reference_type' => Assortment::MORPH_TYPE_ALIAS,
            'rated_reference_id' => $receiptLine->assortment_uuid,
            'rated_by_reference_type' => Client::MORPH_TYPE_ALIAS,
            'rated_by_reference_id' => $self->uuid,
            'rated_through_reference_type' => ReceiptLine::MORPH_TYPE_ALIAS,
            'rated_through_reference_id' => $receiptLine->uuid,
            'additional_attributes->comment' => $comment2
        ]);

        $query = [
            'where' => [[
                'is_rated',
                '=',
                '1'
            ]],
            'order_by' => ['source' => 'ASC']
        ];

        $json = $this->be($self)->json('GET', '/clients/api/profile/purchases-month', $query);
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'source_line_id' => $orderProduct->uuid,
                    'rating_comment' => $comment1,
                ],
                [
                    'source_line_id' => $receiptLine->uuid,
                    'rating_comment' => $comment2,
                ]
            ]
        ]);
    }
}
