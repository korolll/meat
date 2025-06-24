<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Client;
use App\Models\ClientBonusTransaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class ClientBonusTransactionTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    public function testIndex()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var ClientBonusTransaction $transaction */
        $transaction = ClientBonusTransaction::factory()->createOne([
            'reason' => ClientBonusTransaction::REASON_MANUAL,
            'client_uuid' => $self->uuid,
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/client-bonus-transactions');
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
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var ClientBonusTransaction $transaction */
        $transaction = ClientBonusTransaction::factory()->createOne([
            'reason' => ClientBonusTransaction::REASON_MANUAL,
            'client_uuid' => $self->uuid,
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/client-bonus-transactions/' . $transaction->uuid);
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
    protected function setUp(): void
    {
        parent::setUp();

        Auth::shouldUse('api-clients');
    }
}
