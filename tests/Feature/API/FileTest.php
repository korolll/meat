<?php

namespace Tests\Feature\API;

use App\Models\FileCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;

class FileTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
    }

    /**
     * @test
     */
    public function store()
    {
        $uploadedFile = UploadedFile::fake()->image('test.png');

        $self = factory(User::class)->state('store')->create();
        $json = $this->be($self)->postJson('/api/files', [
            'file_category_id' => FileCategory::ID_ASSORTMENT_IMAGE,
            'file' => $uploadedFile,
        ]);

        $json->assertSuccessful()->assertJsonStructure([
            'data' => [
                'uuid',
            ],
        ]);

        Storage::assertExists(
            $uploadedFile->hashName(FileCategory::ID_ASSORTMENT_IMAGE)
        );
    }
}
