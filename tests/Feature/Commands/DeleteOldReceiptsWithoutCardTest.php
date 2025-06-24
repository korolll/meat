<?php

namespace Tests\Feature\Commands;

use App\Models\Product;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class DeleteOldReceiptsWithoutCardTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testCommand()
    {
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->create([
            'created_at' => now()->subMonth(),
            'loyalty_card_uuid' => null,
            'loyalty_card_number' => null,
        ]);
        /** @var Receipt $receipt2 */
        $receipt2 = factory(Receipt::class)->create([
            'created_at' => now()->subDays(4),
            'loyalty_card_uuid' => null,
            'loyalty_card_number' => null,
        ]);
        /** @var ReceiptLine $receiptLine */
        $receiptLine = factory(ReceiptLine::class)->create([
            'receipt_uuid' => $receipt->uuid
        ]);

        $this->artisan('receipts:delete-old-without-card');
        $this->assertDatabaseMissing('receipt_lines', [
            'uuid' => $receiptLine->uuid,
        ]);
        $this->assertDatabaseMissing('receipts', [
            'uuid' => $receipt->uuid,
        ]);
        $this->assertDatabaseHas('receipts', [
            'uuid' => $receipt2->uuid,
        ]);
    }
}
