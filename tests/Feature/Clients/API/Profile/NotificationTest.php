<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Client;
use App\Notifications\API\CustomNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

class NotificationTest extends TestCase
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
     *
     */
    public function testIndex(): void
    {
        Queue::fake();
        /** @var Client $client */
        $client = factory(Client::class)->create();
        $customNotification = new CustomNotification('Test', 'test', []);

        $client->notifyNow($customNotification);
        /** @var DatabaseNotification $notification */
        $notification = $client->notifications->first();
        $this->assertInstanceOf(DatabaseNotification::class, $notification);

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
            'order_by' => [
                'created_at' => 'desc',
            ]
        ];

        $data = [[
            'id' => $notification->id,
            'type' => $notification->type,
            'data' => $notification->data,
            'created_at' => $notification->created_at,
        ]];
        $response = $this->be($client)->json('get', 'clients/api/profile/notifications', $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testRead(): void
    {
        Queue::fake();
        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var DatabaseNotification $notification */
        $notification = $client->notifications()->make();
        $notification->id = Uuid::uuid4()->toString();
        $notification->type = '123';
        $notification->data = '';
        $notification->save();

        $response = $this->be($client)->json('post', 'clients/api/profile/notifications/' . $notification->id . '/read');
        $response->assertSuccessful();

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /**
     *
     */
    public function testReadAll(): void
    {
        Queue::fake();
        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var DatabaseNotification $notification */
        $notification = $client->notifications()->make();
        $notification->id = Uuid::uuid4()->toString();
        $notification->type = '123';
        $notification->data = '';
        $notification->save();

        $response = $this->be($client)->json('post', 'clients/api/profile/notifications/read-all');
        $response->assertSuccessful();

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }
}
