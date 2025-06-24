<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class UserRegisterTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function register()
    {
        $user = factory(User::class)->make([
            'email' => $this->faker->email
        ]);
        $postData = $user->only([
            'user_type_id',
            'full_name',
            'legal_form_id',
            'organization_name',
            'organization_address',
            'address',
            'email',
            'phone',
            'inn',
            'kpp',
            'ogrn',
            'region_uuid',
            'address_latitude',
            'address_longitude',
            'work_hours_from',
            'work_hours_till',
            'brand_name',
        ]);

        $json = $this->postJson('/api/users/register', $postData);

        $data = [
            'email' => $user->email,
            'is_email_verified' => false,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('users', $data);
    }

    /**
     * @test
     */
    public function verifyEmail()
    {
        $user = factory(User::class)->create([
            'is_email_verified' => false,
        ]);


        // Pass уже пропущен через bcrypt. Сократим размер для прохода валидации
        $password = substr($user->password, 0, 50);
        $json = $this->postJson('/api/users/verify-email', [
            'token' => $user->email_verify_token,
            'password' => $password,
        ]);

        $data = [
            'email' => $user->email,
            'is_email_verified' => true,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('users', $data);
    }

    /**
     * @return array
     */
    public function emailVerifiedDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @test
     *
     * @param bool $verified
     *
     * @dataProvider emailVerifiedDataProvider
     */
    public function emailVerified(bool $verified)
    {
        $data = [
            'is_email_verified' => $verified,
            'user_type_id' => UserType::ID_SUPPLIER
        ];

        /** @var User $user */
        $user = factory(User::class)->create($data);
        $json = $this->postJson('/api/users/email-verified', [
            'token' => $user->email_verify_token,
        ]);

        $json->assertSuccessful()->assertJson(compact('data'));
    }
}
