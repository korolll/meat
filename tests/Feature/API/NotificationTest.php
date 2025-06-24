<?php

namespace Tests\Feature\API;

use App\Models\Client;
use App\Models\User;
use App\Notifications\API\CustomNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCaseNotificationsFake;

class NotificationTest extends TestCaseNotificationsFake
{

    use DatabaseTransactions;

    /**
     *
     */
    public function testStore(): void
    {
        Notification::fake();
        /** @var Client $client */
        $client = factory(Client::class)->create();
        /** @var User $user */
        $user = factory(User::class)->state('admin')->create();

        $data = [
            'title' => 'Title',
            'body' => 'Body',
            'meta' => [
                'x' => 1
            ],
            'client_uuids' => [$client->uuid]
        ];

        $response = $this->be($user)->json('post', 'api/notifications', $data);
        $response->assertSuccessful();
        Notification::assertSentTo($client, function (CustomNotification $notification) use ($data) {
            unset($data['client_uuids']);
            return $notification->toArray() === $data;
        });
    }
}
