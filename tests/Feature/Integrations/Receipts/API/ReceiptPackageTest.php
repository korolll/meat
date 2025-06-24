<?php

namespace Tests\Feature\Integrations\Receipts\API;

use App\Models\LoyaltyCard;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class ReceiptPackageTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function store()
    {
        $user = factory(User::class)->state('store')->create();
        $date = now();

        $loyaltyCard = factory(LoyaltyCard::class)->make();
        /**
         * @var $product Product
         */
        $product = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'quantity' => 10,
        ]);

        $self = $user;
        $json = $this->be($self)->postJson('/integrations/receipts/api/receipt-packages', [
            '@type' => 'ReceiptPackage',
            'createDate' => $date,
            'packageId' => 19740,
            'userUuid' => $user->uuid,
            'data' => [
                [
                    'date' => $date,
                    'num' => 31101,
                    'loyaltyCardNumber' => $loyaltyCard->number,
                    'loyaltyCardTypeUuid' => $loyaltyCard->loyalty_card_type_uuid,
                    'sum' => 100,
                    'productList' => [
                        [
                            'barcode' => $product->assortment->barcodes[0]->barcode,
                            'sum' => 100,
                            'quantity' => 0.5,
                        ],
                    ],
                ],
            ],
        ]);

        $json->assertSuccessful()->assertJson([
            'processed' => 1,
        ]);

        $this->assertDatabaseHas('receipts', [
            'user_uuid' => $user->uuid,
        ]);

        $this->assertDatabaseHas('receipt_lines', [
            'product_uuid' => $product->uuid,
            'assortment_uuid' => $product->assortment_uuid,
            'quantity' => 0.5,
        ]);

        $this->assertDatabaseHas('loyalty_cards', [
            'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
            'number' => $loyaltyCard->number,
        ]);

        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product->uuid,
            'quantity_old' => 10,
            'quantity_delta' => -0.5,
            'quantity_new' => 9.5,
        ]);

        $this->assertDatabaseHas('products', [
            'uuid' => $product->uuid,
            'quantity' => 9.5,
        ]);
    }
}
