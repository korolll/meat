<?php

namespace Tests\Feature\Commands;

use App\Models\ClientCreditCard;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Tests\TestCaseNotificationsFake;

class DeleteNotBoundOldClientCreditCardsTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testCommand()
    {
        /** @var ClientCreditCard $card */
        $card = ClientCreditCard::factory()->createOne([
            'binding_id' => null,
            'created_at' => Date::today()->subDay()->subHour()
        ]);

        $this->artisan('client:delete-old-cards');
        $this->assertDatabaseMissing('client_credit_cards', [
            'uuid' => $card->uuid,
        ]);
    }
}
