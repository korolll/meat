<?php

namespace Tests\Feature\Clients\API;

use App\Models\Client;
use App\Models\StoryTab;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoryTest extends TestCase
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
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var StoryTab $storyTab */
        $storyTab = StoryTab::factory()->createOne();
        $story = $storyTab->story;

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [[
            'id' => $story->id,
            'name' => $story->name,
            'logo_file_path' => Storage::url($story->logoFile->path),
            'tabs' => [[
                'id' => $storyTab->id,
                'title' => $storyTab->title,
                'text' => $storyTab->text,

                'text_color' => $storyTab->text_color,
                'duration' => $storyTab->duration,
                'button_title' => $storyTab->button_title,
                'url' => $storyTab->url,
                'logo_file_path' => Storage::url($storyTab->logoFile->path),
            ]]
        ]];
        $response = $this->be($self)->json('get', '/clients/api/stories', $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testShow(): void
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var StoryTab $storyTab */
        $storyTab = StoryTab::factory()->createOne();
        $story = $storyTab->story;

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [
            'id' => $story->id,
            'name' => $story->name,
            'logo_file_path' => Storage::url($story->logoFile->path),
            'tabs' => [[
                'id' => $storyTab->id,
                'title' => $storyTab->title,
                'text' => $storyTab->text,

                'text_color' => $storyTab->text_color,
                'duration' => $storyTab->duration,
                'button_title' => $storyTab->button_title,
                'url' => $storyTab->url,
                'logo_file_path' => Storage::url($storyTab->logoFile->path),
            ]]
        ];
        $response = $this->be($self)->json('get', '/clients/api/stories/' . $story->id, $query);
        $response->assertSuccessful()->assertJson(compact('data'));
        $story->refresh();
    }
}
