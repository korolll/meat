<?php

namespace Tests\Feature\API;

use App\Models\Client;
use App\Models\NotificationTask;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class NotificationTaskTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    public function testIndex()
    {
        /** @var $notificationTask NotificationTask */
        $notificationTask = NotificationTask::factory()->createOne();

        /** @var $self User */
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/notification-tasks?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $notificationTask->uuid
        ]);
    }

    /**
     * @return void
     */
    public function testStore()
    {
        $options = [
            'test' => $this->faker->uuid,
            'meta' => ['x' => $this->faker->uuid]
        ];

        /** @var $notificationTask NotificationTask */
        $notificationTask = NotificationTask::factory()->makeOne([
            'options' => $options
        ]);
        $self = factory(User::class)->state('admin')->create();

        $data = $notificationTask->only([
            'title_template',
            'body_template',
            'options',
            'execute_at',
        ]);

        $json = $this->be($self)->postJson('/api/notification-tasks', $data);
        $json->assertSuccessful()->assertJson(compact('data'));

        $uuid = $json->json('data.uuid');
        $notificationTask = NotificationTask::find($uuid);
        $this->assertEquals($data, $notificationTask->only([
            'title_template',
            'body_template',
            'options',
            'execute_at',
        ]));
    }

    /**
     * @return void
     */
    public function testUpdate()
    {
        $options = [
            'test' => $this->faker->uuid
        ];

        $clients = factory(Client::class)->times(2)->create();
        /** @var $notificationTask NotificationTask */
        $notificationTask = NotificationTask::factory()->createOne();
        $notificationTask->clients()->sync($clients[0]->uuid);
        $self = factory(User::class)->state('admin')->create();

        $newNotificationTask = NotificationTask::factory()->makeOne([
            'options' => $options
        ]);
        $data = $newNotificationTask->only([
            'title_template',
            'body_template',
            'options',
            'execute_at',
        ]);

        $data['client_uuids'] = [$clients[1]->uuid];

        $json = $this->be($self)->putJson('/api/notification-tasks/' . $notificationTask->uuid, $data);
        $json->assertSuccessful()->assertJson(compact('data'));

        $notificationTask->refresh();
        $check = $notificationTask->only([
            'title_template',
            'body_template',
            'options',
            'execute_at',
        ]);
        $check['client_uuids'] = $notificationTask->clients()->pluck('uuid')->toArray();
        $this->assertEquals($data, $check);
    }

    /**
     * @test
     */
    public function testView()
    {
        /** @var $notificationTask NotificationTask */
        $notificationTask = NotificationTask::factory()->createOne();
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/notification-tasks/' . $notificationTask->uuid);
        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $notificationTask->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function testDelete()
    {
        /** @var $notificationTask NotificationTask */
        $notificationTask = NotificationTask::factory()->createOne();
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson('/api/notification-tasks/' . $notificationTask->uuid);
        $json->assertSuccessful();

        $notificationTask->refresh();
        $this->assertNotNull($notificationTask->deleted_at);
    }
}
