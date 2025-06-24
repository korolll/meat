<?php

namespace Tests\Feature\API;

use App\Models\StoryTab;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Tests\TestCaseNotificationsFake;

class StoryTabTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var StoryTab $storyTab */
        $storyTab = StoryTab::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [[
            'id' => $storyTab->id,
            'story_id' => $storyTab->story_id,
            'title' => $storyTab->title,
        ]];
        $response = $this->be($self, 'api')->json('get', '/api/story-tabs', $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testShow(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var StoryTab $storyTab */
        $storyTab = StoryTab::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [
            'id' => $storyTab->id,
            'story_id' => $storyTab->story_id,
            'title' => $storyTab->title,
        ];
        $response = $this->be($self, 'api')->json('get', '/api/story-tabs/' . $storyTab->id, $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testCreate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var StoryTab $storyTab */
        $storyTab = StoryTab::factory()->makeOne();

        // Next test collection
        $data = [
            'story_id' => $storyTab->story_id,
            'title' => $storyTab->title,
            'text' => $storyTab->text,
            'text_color' => $storyTab->text_color,
            'duration' => $storyTab->duration,
            'button_title' => $storyTab->button_title,
            'logo_file_uuid' => $storyTab->logoFile->uuid,
        ];

        $response = $this->be($self, 'api')->json('post', '/api/story-tabs', $data);
        $response->assertSuccessful()->assertJson([
            'data' => Arr::except($data, ['logo_file_uuid'])
        ]);
        $this->assertDatabaseHas('story_tabs', $data);
    }

    /**
     *
     */
    public function testUpdate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var StoryTab $storyTabOld */
        $storyTabOld = StoryTab::factory()->createOne();
        /** @var StoryTab $storyTabNew */
        $storyTabNew = StoryTab::factory()->makeOne();

        // Next test collection
        $data = [
            'story_id' => $storyTabNew->story_id,
            'title' => $storyTabNew->title,
            'text' => $storyTabNew->text,
            'text_color' => $storyTabNew->text_color,
            'duration' => $storyTabNew->duration,
            'button_title' => $storyTabNew->button_title,
            'logo_file_uuid' => $storyTabNew->logoFile->uuid,
        ];
        $response = $this->be($self, 'api')->json('put', '/api/story-tabs/' . $storyTabOld->id, $data);
        $response->assertSuccessful()->assertJson([
            'data' => Arr::except($data, ['logo_file_uuid'])
        ]);
        $this->assertDatabaseHas('story_tabs', $data);
    }

    /**
     *
     */
    public function testDelete(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var StoryTab $storyTabOld */
        $storyTab = StoryTab::factory()->createOne();
        $response = $this->be($self, 'api')->json('delete', '/api/story-tabs/' . $storyTab->id);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $storyTab->refresh();
        $this->assertSoftDeleted($storyTab);
    }
}
