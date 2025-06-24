<?php

namespace Tests\Feature\API;

use App\Models\Assortment;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCaseNotificationsFake;

class TagTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;


    /**
     * @param null|string $filterBy
     *
     * @test
     * @testWith [null]
     *           ["name"]
     *           ["fixed_in_filters"]
     */
    public function index(?string $filterBy)
    {
        $tag = factory(Tag::class)->create();
        $self = factory(User::class)->state('admin')->create();

        $data = [];
        if ($filterBy) {
            $data = ['where' => [[$filterBy, '=', $tag->{$filterBy}]]];
        }

        $json = $this->be($self)->json('get', '/api/tags?per_page=1000', $data);
        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $tag->uuid,
            'name' => $tag->name,
            'fixed_in_filters' => $tag->fixed_in_filters,
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $tag = factory(Tag::class)->make();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/tags', $tag->only([
            'name',
            'fixed_in_filters',
        ]));

        $data = [
            'uuid' => $json->json('data.uuid'),
            'name' => $tag->name,
            'fixed_in_filters' => $tag->fixed_in_filters,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('tags', $data);
    }

    /**
     * @test
     */
    public function storeWithUniqueError()
    {
        $exist = factory(Tag::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/tags', $exist->only([
            'name',
        ]));

        $json->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function show()
    {
        $tag = factory(Tag::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/tags/{$tag->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $tag->uuid,
                'name' => $tag->name,
                'fixed_in_filters' => $tag->fixed_in_filters,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $tagOld = factory(Tag::class)->create();
        $tagNew = factory(Tag::class)->make();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/tags/{$tagOld->uuid}", $tagNew->only([
            'name',
            'fixed_in_filters',
        ]));

        $data = [
            'uuid' => $tagOld->uuid,
            'name' => $tagNew->name,
            'fixed_in_filters' => $tagNew->fixed_in_filters,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('tags', $data);
    }

    /**
     * @test
     */
    public function updateAndGetUniqueError()
    {
        $tagOld = factory(Tag::class)->create();
        $tagToUpdate = factory(Tag::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/tags/{$tagToUpdate->uuid}", $tagOld->only([
            'name',
        ]));

        $json->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function destroy()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();
        /** @var Collection $assortments */
        $assortments = factory(Assortment::class)->times(2)->create();
        $tag->assortments()->sync($assortments->pluck('uuid'));

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/tags/{$tag->uuid}");

        $data = [
            'uuid' => $tag->uuid,
            'name' => $tag->name,
        ];

        $json->assertSuccessful()->assertJson(['data' => $data]);

        $this->assertDatabaseMissing('tags', $data);
        $this->assertDatabaseMissing('assortment_tag', [
            'tag_uuid' => $tag->uuid
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

        $self = factory(User::class)->create();
        $json = $this->be($self)->getJson('/api/tags/search?phrase=кошки+сер');

        $json->assertSuccessful()->assertJson([
            'data' => [
                Str::lower($tag->name),
            ],
        ]);
    }
}
