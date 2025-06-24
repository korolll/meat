<?php

namespace Tests\Feature\Clients\API;

use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCaseNotificationsFake;

class TagTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $tag = factory(Tag::class)->create();
        $json = $this->getJson('/clients/api/tags?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $tag->uuid,
            'name' => $tag->name,
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $tag = factory(Tag::class)->create();
        $json = $this->getJson("/clients/api/tags/{$tag->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $tag->uuid,
                'name' => $tag->name,
            ],
        ]);
    }

    /**
     * @test
     */
    public function search()
    {
        $tag = factory(Tag::class)->create([
            'name' => 'Серая кошка и мышка',
        ]);

        $json = $this->getJson('/clients/api/tags/search?phrase=кошки+сер');
        $json->assertSuccessful()->assertJson([
            'data' => [
                Str::lower($tag->name),
            ],
        ]);
    }
}
