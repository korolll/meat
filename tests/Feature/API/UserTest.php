<?php

namespace Tests\Feature\API;

use App\Models\File;
use App\Models\FileCategory;
use App\Models\User;
use App\Models\UserVerifyStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseNotificationsFake;
use Illuminate\Http\Response;

class UserTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @param string $userState
     *
     * @test
     * @testWith ["admin"]
     *           ["supplier"]
     */
    public function index($userState)
    {
        $self = factory(User::class)->state($userState)->create();
        $json = $this->be($self)->getJson('/api/users?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment([
            'uuid' => $self->uuid,
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/users/{$self->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $self->uuid,
            ],
        ]);
    }

    /**
     * @return array
     */
    public function verifyDataProvider()
    {
        return [
            [UserVerifyStatus::ID_APPROVED],
            [UserVerifyStatus::ID_DECLINED, 'Test'],
            [UserVerifyStatus::ID_DECLINED, null, false],
            ['_not_user_verify_status_', null, false],
        ];
    }

    /**
     * @param $status
     * @param null $comment
     * @param bool $assertSuccess
     *
     * @test
     *
     * @dataProvider verifyDataProvider
     */
    public function verify($status, $comment = null, $assertSuccess = true)
    {
        $self = factory(User::class)->states('admin')->create();
        $user = factory(User::class)->create([
            'user_verify_status_id' => UserVerifyStatus::ID_NEW,
        ]);

        $json = $this->be($self)->putJson("/api/users/{$user->uuid}/verify", [
            'user_verify_status_id' => $status,
            'comment' => $comment,
        ]);

        if (!$assertSuccess) {
            $json->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
            return;
        }

        $data = [
            'uuid' => $user->uuid,
            'user_verify_status_id' => $status,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('users', $data);
    }

    /**
     * @test
     */
    public function testUpdate()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();
        /** @var User $user */
        $user = factory(User::class)->make();
        $file = factory(File::class)->create([
            'user_uuid' => $self->uuid,
            'file_category_id' => FileCategory::ID_USER_FILE,
        ]);

        $json = $this->be($self)->putJson('/api/users/' . $anotherUser->uuid, array_merge($user->only([
            'user_type_id',
            'full_name',
            'legal_form_id',
            'organization_name',
            'organization_address',
            'address',
            'phone',
            'password',
            'inn',
            'kpp',
            'ogrn',
            'region_uuid',
        ]), [
            'files' => [
                ['uuid' => $file->uuid, 'public_name' => 'hello kitty'],
            ],
        ]));

        $data = [
            'uuid' => $anotherUser->uuid,
            'ogrn' => $user->ogrn,
            'region_uuid' => $user->region_uuid,
            'files' => [
                [
                    'uuid' => $file->uuid,
                    'path' => Storage::url($file->path),
                    'public_name' => 'hello kitty',
                ],
            ],
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('users', Arr::except($data, 'files'));
    }
}
