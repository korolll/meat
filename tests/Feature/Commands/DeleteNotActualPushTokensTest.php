<?php

namespace Tests\Feature\Commands;

use App\Models\Client;
use App\Models\ClientPushToken;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class DeleteNotActualPushTokensTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testCommand()
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var ClientPushToken[] $pushTokens */
        $pushTokens = ClientPushToken::factory()->createMany([
            ['client_uuid' => $client->uuid],
            ['client_uuid' => $client->uuid, 'updated_at' => now()->subDay()],
        ]);

        /** @var Client $client2 */
        $client2 = factory(Client::class)->create();
        /** @var ClientPushToken[] $pushTokens2 */
        $pushTokens2 = ClientPushToken::factory()->createMany([
            ['client_uuid' => $client2->uuid],
            ['client_uuid' => $client2->uuid, 'updated_at' => now()->subDay()],
        ]);

        /** @var Client $client3 */
        $client3 = factory(Client::class)->create();
        /** @var ClientPushToken[] $pushTokens3 */
        $pushTokens3 = ClientPushToken::factory()->createMany([
            ['client_uuid' => $client3->uuid],
            ['client_uuid' => $client3->uuid, 'updated_at' => now()->subDays(2)],
            ['client_uuid' => $client3->uuid, 'updated_at' => now()->subDay()],
        ]);

        $this->artisan('push-tokens:delete-not-actual --bulk-size=2');
        $this->assertDatabaseHas(ClientPushToken::class, [
            'id' => $pushTokens[0]->id
        ]);
        $this->assertDatabaseHas(ClientPushToken::class, [
            'id' => $pushTokens2[0]->id
        ]);
        $this->assertDatabaseHas(ClientPushToken::class, [
            'id' => $pushTokens3[0]->id
        ]);
        $this->assertDatabaseCount(ClientPushToken::class, 3);
    }
}
