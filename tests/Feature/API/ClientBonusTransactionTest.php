<?php

namespace Tests\Feature\API;

use App\Models\ClientBonusTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class ClientBonusTransactionTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    public function testIndex()
    {
        $self = factory(User::class)->state('admin')->create();

        /** @var ClientBonusTransaction $transaction */
        $transaction = ClientBonusTransaction::factory()->createOne([
            'reason' => ClientBonusTransaction::REASON_MANUAL
        ]);

        $json = $this->be($self)->getJson('/api/client-bonus-transactions');
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $transaction->uuid,
                    'client_uuid' => $transaction->client_uuid,
                ],
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testShow()
    {
        $self = factory(User::class)->state('admin')->create();

        /** @var ClientBonusTransaction $transaction */
        $transaction = ClientBonusTransaction::factory()->createOne([
            'reason' => ClientBonusTransaction::REASON_MANUAL
        ]);

        $json = $this->be($self)->getJson('/api/client-bonus-transactions/' . $transaction->uuid);
        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $transaction->uuid,
                'client_uuid' => $transaction->client_uuid,
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testStore()
    {
        $self = factory(User::class)->state('admin')->create();

        /** @var ClientBonusTransaction $transaction */
        $transaction = ClientBonusTransaction::factory()->makeOne();

        $json = $this->be($self)->postJson('/api/client-bonus-transactions', [
            'client_uuid' => $transaction->client_uuid,
            'delta' => $transaction->quantity_delta
        ]);

        $json->assertSuccessful()->assertJson([
            'data' => [
                'client_uuid' => $transaction->client_uuid,
                'quantity_delta' =>  $transaction->quantity_delta,
            ],
        ]);

        $this->assertDatabaseHas('client_bonus_transactions', [
            'client_uuid' => $transaction->client_uuid,
            'quantity_delta' =>  $transaction->quantity_delta,
        ]);
    }
}
