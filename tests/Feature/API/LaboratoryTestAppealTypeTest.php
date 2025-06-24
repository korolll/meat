<?php

namespace Tests\Feature\API;

use App\Models\LaboratoryTestAppealType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class LaboratoryTestAppealTypeTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $type = factory(LaboratoryTestAppealType::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/laboratory-test-appeal-types?per-page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $type->uuid,
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $type = factory(LaboratoryTestAppealType::class)->make();

        $data = $type->only([
            'name'
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/laboratory-test-appeal-types', $data);

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('laboratory_test_appeal_types', $data);
    }

    /**
     * @test
     */
    public function show()
    {
        $type = factory(LaboratoryTestAppealType::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/laboratory-test-appeal-types/{$type->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $type->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $typeOld = factory(LaboratoryTestAppealType::class)->create();
        $typeNew = factory(LaboratoryTestAppealType::class)->make();

        $data = $typeNew->only([
            'name'
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/laboratory-test-appeal-types/{$typeOld->uuid}", $data);

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('laboratory_test_appeal_types', $data);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $type = factory(LaboratoryTestAppealType::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/laboratory-test-appeal-types/{$type->uuid}");

        $json->assertSuccessful();
    }
}
