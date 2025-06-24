<?php

namespace Tests\Feature\API;

use App\Models\Draft;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class DraftTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function store()
    {
        $draft = factory(Draft::class)->make();

        $self = $draft->user;
        $json = $this->be($self)->postJson('/api/profile/drafts', $draft->only([
            'name',
            'attributes',
        ]));

        $json->assertSuccessful();
        $this->assertDatabaseHas('drafts', [
            'user_uuid' => $draft->user_uuid,
            'name' => $draft->name,
            'attributes' => json_encode($draft->attributes),
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $draft = factory(Draft::class)->create();

        $self = $draft->user;
        $json = $this->be($self)->getJson("/api/profile/drafts/{$draft->name}");

        $json->assertSuccessful()->assertJson([
            'data' => $draft->only('name', 'attributes'),
        ]);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $draft = factory(Draft::class)->create();

        $self = $draft->user;
        $json = $this->be($self)->deleteJson("/api/profile/drafts/{$draft->name}");

        $json->assertSuccessful();
        $this->assertDatabaseMissing('drafts', [
            'user_uuid' => $draft->user_uuid,
            'name' => $draft->name,
            'attributes' => json_encode($draft->attributes),
        ]);
    }
}
