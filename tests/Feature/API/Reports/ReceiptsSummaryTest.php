<?php

namespace Tests\Feature\API\Reports;

use App\Models\LoyaltyCard;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCaseNotificationsFake;

class ReceiptsSummaryTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     *
     * @testWith [false]
     *           [true]
     */
    public function index(bool $useAdmin = false)
    {
        /** @var User $user */
        $user = factory(User::class)->state('store')->create();
        if ($useAdmin) {
            $beUser = factory(User::class)->state('admin')->create();
        } else {
            $beUser = $user;
        }

        $createdAt = Date::createFromDate(2019,1,2)->setTime(0,0,0);
        factory(Receipt::class)->create([
            'user_uuid' => $user->uuid,
            'created_at' => $createdAt,
            'total' => 10,
            'loyalty_card_uuid' => null
        ]);

        $createdAtTwo = Date::createFromDate(2019,1,5)->setTime(0,0,0);
        $receiptTwo = factory(Receipt::class)->create([
            'user_uuid' => $user->uuid,
            'created_at' => $createdAtTwo,
            'total' => 20,
            'loyalty_card_uuid' => null
        ]);

        $groupBy = 'day';
        $data = [
            'date_start' => Date::createFromDate(2019,1,1),
            'date_end' => Date::createFromDate(2019,1,10),
            'group_by' => $groupBy,
            'order_by' => ['date' => 'asc'],
            'loyalty_card_is_applied' => false
        ];

        $json = $this->be($beUser)->json('get', '/api/reports/receipts-summary', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'date' => $createdAt,
                    'quantity' => 1,
                    'total' => 10,
                ],
                [
                    'date' => $createdAtTwo,
                    'quantity' => 1,
                    'total' => 20,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function export()
    {
        /** @var User $user */
        $user = factory(User::class)->state('store')->create();

        /** @var LoyaltyCard $card */
        $card = factory(LoyaltyCard::class)->create();
        factory(Receipt::class)->create([
            'user_uuid' => $user->uuid,
            'loyalty_card_uuid' => $card->uuid,
        ]);

        $data = [
            'date_start' => now()->subDays(3),
            'date_end' => now()->addMinute(),
            'group_by' => 'day',
            'loyalty_card_is_applied' => true
        ];

        Excel::fake();

        $json = $this->be($user)->json('get', '/api/reports/receipts-summary/xlsx', $data);
        $json->assertSuccessful();

        Excel::assertDownloaded('receipts.xlsx');
    }
}
