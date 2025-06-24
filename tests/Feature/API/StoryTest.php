<?php

namespace Tests\Feature\API;

use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCaseNotificationsFake;

class StoryTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Story $story */
        $story = Story::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [[
            'id' => $story->id,
            'name' => $story->name,
        ]];
        $response = $this->be($self, 'api')->json('get', '/api/stories', $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testShow(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Story $story */
        $story = Story::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [
            'id' => $story->id,
            'name' => $story->name,
        ];
        $response = $this->be($self, 'api')->json('get', '/api/stories/' . $story->id, $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testCreate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Story $story */
        $story = Story::factory()->makeOne();

        // Next test collection
        $data = [
            'name' => $story->name,
            'show_from' => $story->show_from->format('Y-m-d H:i:sO'),
            'show_to' => $story->show_to->format('Y-m-d H:i:sO'),
            'logo_file_uuid' => $story->logoFile->uuid,
        ];
        $response = $this->be($self, 'api')->json('post', '/api/stories', $data);

        $response->assertSuccessful()->assertJson([
            'data' => [
                'name' => $story->name,
                'show_from' => $story->show_from->format('Y-m-d H:i:sO'),
                'show_to' => $story->show_to->format('Y-m-d H:i:sO'),
            ]
        ]);
        $this->assertDatabaseHas('stories', [
            'name' => $story->name
        ]);
    }

    /**
     *
     */
    public function testUpdate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Story $storyOld */
        $storyOld = Story::factory()->createOne();
        /** @var Story $storyNew */
        $storyNew = Story::factory()->makeOne();

        // Next test collection
        $data = [
            'name' => $storyNew->name,
            'show_from' => $storyNew->show_from->format('Y-m-d H:i:sO'),
            'show_to' => $storyNew->show_to->format('Y-m-d H:i:sO'),
            'logo_file_uuid' => $storyNew->logoFile->uuid,
        ];
        $response = $this->be($self, 'api')->json('put', '/api/stories/' . $storyOld->id, $data);

        $response->assertSuccessful()->assertJson([
            'data' => [
                'name' => $storyNew->name,
                'show_from' => $storyNew->show_from->format('Y-m-d H:i:sO'),
                'show_to' => $storyNew->show_to->format('Y-m-d H:i:sO'),
            ]
        ]);
        $this->assertDatabaseHas('stories', [
            'name' => $storyNew->name
        ]);
    }

    /**
     *
     */
    public function testDelete(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Story $storyOld */
        $story = Story::factory()->createOne();
        $response = $this->be($self, 'api')->json('delete', '/api/stories/' . $story->id);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $story->refresh();
        $this->assertSoftDeleted($story);
    }
}
