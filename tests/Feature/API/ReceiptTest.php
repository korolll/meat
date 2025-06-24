<?php

namespace Tests\Feature\API;

use App\Models\File;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class ReceiptTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex()
    {
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->state('has-lines')->create();
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        $reqData = [
            'where' => [[
                'client_uuid',
                '=',
                $receipt->loyaltyCard->client_uuid
            ]],
            'order_by' => ['client_uuid' => 'ASC']
        ];
        $json = $this->be($self)->json('GET', '/api/receipts', $reqData);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $receipt->uuid,
                    'store_brand_name' => $receipt->user->brand_name,
                    'store_address' => $receipt->user->address,
                    'client_uuid' => $receipt->loyaltyCard->client_uuid,
                    'loyalty_card_types' => $receipt->user->loyaltyCardTypes->map->only('uuid')->all(),
                    'receipt_lines_count' => $receipt->receiptLines->count()
                ],
            ],
        ]);
    }

    /**
     *
     */
    public function testShow()
    {
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->state('has-lines')->create();
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/receipts/{$receipt->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $receipt->uuid,
                'receipt_lines_count' => $receipt->receiptLines->count()
            ],
        ]);
    }



    /**
     * @test
     */
    public function testLines()
    {
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->state('has-lines')->create();
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/receipts/{$receipt->uuid}/lines");

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
}
