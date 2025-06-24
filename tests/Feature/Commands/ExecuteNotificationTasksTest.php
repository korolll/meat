<?php

namespace Tests\Feature\Commands;

use App\Models\Client;
use App\Models\NotificationTask;
use App\Notifications\API\CustomNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCaseNotificationsFake;

class ExecuteNotificationTasksTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testCommand()
    {
        $titleTemplate = 'Привет {name}';
        $bodyTemplate = $this->faker->uuid;
        $options = [
            'meta' => ['x' => $this->faker->uuid]
        ];

        /** @var NotificationTask $task */
        $task = NotificationTask::factory()->createOne([
            'execute_at' => now()->subMinute(),
            'taken_to_work_at' => null,
            'title_template' => $titleTemplate,
            'body_template' => $bodyTemplate,
            'options' => $options
        ]);

        /** @var Client $client */
        $client = factory(Client::class)->create();

        $this->artisan('notification-tasks:execute');
        Notification::assertSentTo($client, CustomNotification::class, function (CustomNotification $notification) use ($client, $titleTemplate, $bodyTemplate, $options) {
            $data = $notification->toArray();
            $this->assertEquals([
                'title' => 'Привет ' . $client->name,
                'body' => $bodyTemplate,
                'meta' => $options['meta']
            ], $data);
            return true;
        });

        $task->refresh();
        $this->assertNotNull($task->taken_to_work_at);
        $this->assertNotNull($task->executed_at);
    }
}
