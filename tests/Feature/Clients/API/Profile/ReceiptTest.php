<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Receipt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
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

        Auth::shouldUse('api-clients');
    }

    /**
     * @test
     */
    public function index()
    {
        $receipt = factory(Receipt::class)->create();

        $self = $receipt->loyaltyCard->client;
        $json = $this->be($self)->getJson('/clients/api/profile/receipts');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $receipt->uuid,
                    'store_brand_name' => $receipt->user->brand_name,
                    'store_address' => $receipt->user->address,
                    'loyalty_card_types' => $receipt->user->loyaltyCardTypes->map->only('uuid')->all(),
                    'receipt_lines_count' => $receipt->receiptLines->count()
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $receipt = factory(Receipt::class)->create();

        $self = $receipt->loyaltyCard->client;
        $json = $this->be($self)->getJson("/clients/api/profile/receipts/{$receipt->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $receipt->uuid,
            ],
        ]);
    }
}
