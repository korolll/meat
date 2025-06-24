<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\File;
use App\Models\PromoDiverseFoodClientStat;
use App\Models\PromoDiverseFoodClientStatAssortment;
use App\Models\RatingType;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class ReceiptLinesTest extends TestCaseNotificationsFake
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
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->state('has-lines')->create();

        $self = $receipt->loyaltyCard->client;
        $json = $this->be($self)->getJson("/clients/api/profile/receipts/{$receipt->uuid}/lines");

        $data = $receipt->receiptLines->map(function (ReceiptLine $line) {
            return [
                'uuid' => $line->uuid,
                'assortment_images' => $line->assortment->images->map(function (File $file) {
                    return [
                        'uuid' => $file->uuid,
                        'path' => Storage::url($file->path),
                    ];
                })->all(),
            ];
        })->all();

        $json->assertSuccessful()->assertJson([
            'data' => $data,
        ]);
    }

    /**
     * @test
     */
    public function setRating()
    {
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->state('has-lines')->create();
        /** @var ReceiptLine $receiptLine */
        $receiptLine = $receipt->receiptLines()->first();
        $self = $receipt->loyaltyCard->client;
        /** @var PromoDiverseFoodClientStat $stat */
        $stat = PromoDiverseFoodClientStat::factory()->createOne([
            'client_uuid' => $self->uuid,
            'month' => now()->format('Y-m')
        ]);
        PromoDiverseFoodClientStatAssortment::factory()->createOne([
            'promo_diverse_food_client_stat_uuid' => $stat->uuid,
            'assortment_uuid' => $receiptLine->assortment_uuid,
            'is_rated' => false
        ]);

        $json = $this->be($self)->putJson(
            "/clients/api/profile/receipts/{$receipt->uuid}/lines/{$receiptLine->uuid}/set-rating",
            [
                'value' => 4,
                'comment' => 'hello kitty',
            ]
        );

        $json->assertSuccessful();
        $this->assertDatabaseHas('rating_scores', [
            'rated_reference_type' => Assortment::MORPH_TYPE_ALIAS,
            'rated_reference_id' => $receiptLine->assortment_uuid,
            'rated_by_reference_type' => Client::MORPH_TYPE_ALIAS,
            'rated_by_reference_id' => $receipt->loyaltyCard->client_uuid,
            'rated_through_reference_type' => ReceiptLine::MORPH_TYPE_ALIAS,
            'rated_through_reference_id' => $receiptLine->uuid,
            'value' => 4,
            'additional_attributes->comment' => 'hello kitty',
            'additional_attributes->weight' => 1,
        ]);

        $this->assertDatabaseHas('ratings', [
            'reference_type' => Assortment::MORPH_TYPE_ALIAS,
            'reference_id' => $receiptLine->assortment_uuid,
            'rating_type_id' => RatingType::ID_COMMON,
            'value' => 4.0,
        ]);

        $this->assertDatabaseHas('promo_diverse_food_client_stats', [
            'client_uuid' => $self->uuid,
            'purchased_count' => $stat->purchased_count,
            'rated_count' => $stat->rated_count + 1,
        ]);
    }
}
