<?php

namespace Tests\Feature\API\Profile\LaboratoryTests;

use App\Models\File;
use App\Models\FileCategory;
use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class ExecutorLaboratoryTestTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $self = factory(User::class)->state('laboratory')->create();
        $laboratoryTest = factory(LaboratoryTest::class)->state('new')->create([
            'executor_user_uuid' => $self->uuid
        ]);
        $json = $this->be($self)->getJson("/api/profile/laboratory-tests/executor");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $laboratoryTest->uuid
                ]
            ],
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $self = factory(User::class)->state('laboratory')->create();
        $laboratoryTest = factory(LaboratoryTest::class)->state('new')->create([
            'executor_user_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson("/api/profile/laboratory-tests/executor/{$laboratoryTest->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $laboratoryTest->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function setStatusNew()
    {
        $self = factory(User::class)->state('laboratory')->create();
        $laboratoryTest = factory(LaboratoryTest::class)->state('in-work')->create([
            'executor_user_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->putJson("/api/profile/laboratory-tests/executor/{$laboratoryTest->uuid}/set-status", [
            'laboratory_test_status_id' => LaboratoryTestStatus::ID_NEW
        ]);
        $json->assertSuccessful();
    }

    /**
     * @test
     */
    public function setStatusDone()
    {
        $self = factory(User::class)->state('laboratory')->create();
        $file = factory(File::class)->create([
            'user_uuid' => $self->uuid,
            'file_category_id' => FileCategory::ID_LABORATORY_TEST_FILE_EXECUTOR,
        ]);

        $laboratoryTest = factory(LaboratoryTest::class)->state('in-work')->create([
            'executor_user_uuid' => $self->uuid,
        ]);

        $json = $this->be($self)->putJson("/api/profile/laboratory-tests/executor/{$laboratoryTest->uuid}/set-status", [
            'laboratory_test_status_id' => LaboratoryTestStatus::ID_DONE,
            'executor_files' => [['uuid' => $file->uuid, 'public_name' => 'some public name']]
        ]);

        $json->assertSuccessful();
        $this->assertDatabaseHas('file_laboratory_test', [
            'file_uuid' => $file->uuid,
            'laboratory_test_uuid' => $laboratoryTest->uuid,
            'file_category_id' => FileCategory::ID_LABORATORY_TEST_FILE_EXECUTOR,
        ]);
    }
}
