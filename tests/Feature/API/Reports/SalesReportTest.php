<?php

namespace Tests\Feature\API\Reports;

use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Tests\TestCaseNotificationsFake;

class SalesReportTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex()
    {
        /** @var User $user */
        $user = factory(User::class)->state('store')->create();
        $createdAt = Date::createFromDate(2019,1,2)->setTime(0,0,0);
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->create([
            'user_uuid' => $user->uuid,
            'created_at' => $createdAt,
            'total' => 10,
        ]);
        $receipt->refresh();
        /** @var ReceiptLine $receiptLine */
        $receiptLine = factory(ReceiptLine::class)->create([
            'receipt_uuid' => $receipt->uuid,
            'total' => 111,
            'price_with_discount' => null,
            'discount' => null,
        ]);
        $receiptLine->refresh();
        /** @var ReceiptLine $receiptLineTwo */
        $receiptLineTwo = factory(ReceiptLine::class)->create([
            'receipt_uuid' => $receipt->uuid,
            'assortment_uuid' => null,
            'product_uuid' => null,
        ]);
        $receiptLineTwo->refresh();

        $createdAtTwo = Date::createFromDate(2019,1,5)->setTime(0,0,0);
         /** @var Receipt $receiptTwo */
        $receiptTwo = factory(Receipt::class)->create([
            'user_uuid' => $user->uuid,
            'created_at' => $createdAtTwo,
            'total' => 20,
        ]);
        $receiptTwo->refresh();

        $data = [
            'date_start' => Date::createFromDate(2019,1,1),
            'date_end' => Date::createFromDate(2019,1,10),
            'store_uuid' => $user->uuid
        ];

        $json = $this->be($user)->json('get', '/api/reports/sales-report', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'date' => $receipt->created_at,
                    'total' => $receipt->total,
                    'receiptLines' => [
                        [
                            'assortment_uuid' => $receiptLine->assortment_uuid,
                            'assortment_name' => $receiptLine->assortment->name,
                            'barcode' => $receiptLine->barcode,
                            'quantity' => $receiptLine->quantity,
                            'total' => $receiptLine->total,
                        ],
                        [
                            'assortment_uuid' => null,
                            'assortment_name' => null,
                            'barcode' => $receiptLineTwo->barcode,
                            'quantity' => $receiptLineTwo->quantity,
                            'total' => $receiptLineTwo->total,
                        ]
                    ]
                ],
            ],
        ]);
    }

//    /**
//     * @test
//     */
//    public function export()
//    {
//        $user = factory(User::class)->state('store')->create();
//
//        $receipt = factory(Receipt::class)->create([
//            'user_uuid' => $user->uuid,
//        ]);
//
//        $data = [
//            'date_start' => now()->subDays(3),
//            'date_end' => now()->addMinute(),
//            'group_by' => 'day',
//        ];
//
//        Excel::fake();
//
//        $json = $this->be($user)->json('get', '/api/reports/receipts-summary/xlsx', $data);
//        $json->assertSuccessful();
//
//        Excel::assertDownloaded('receipts.xlsx');
//    }
}
