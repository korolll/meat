<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Notifications\API\UserResetPassword;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCaseNotificationsFake;

class UserPasswordResetTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function resetPassword()
    {
        $user = factory(User::class)->create();

        $this->requestPasswordResetToken($user)->assertSuccessful()->assertJson([
            'message' => 'Password reset link was successfully sent',
        ]);

        Notification::assertSentTo($user, UserResetPassword::class);
    }

    /**
     * @test
     */
    public function setPassword()
    {
        $user = factory(User::class)->create();

        $password = Factory::create()->password(8, 50);

        $json = $this->postJson('/api/users/reset-password/set-password', [
            'token' => $this->makePasswordResetToken($user),
            'password' => $password,
        ]);

        $json->assertSuccessful()->assertJson([
            'message' => 'Password was successfully updated',
        ]);

        $this->assertTrue(Auth::validate([
            'email' => $user->email,
            'password' => $password,
        ]));
    }

    /**
     * @test
     */
    public function validateToken()
    {
        $user = factory(User::class)->create();

        $json = $this->postJson('/api/users/reset-password/validate-token', [
            'token' => $this->makePasswordResetToken($user),
        ]);

        $json->assertSuccessful()->assertJson([
            'message' => 'Password reset token is valid',
        ]);
    }

    /**
     * @param User $user
     * @return TestResponse
     */
    protected function requestPasswordResetToken(User $user): TestResponse
    {
        return $this->postJson('/api/users/reset-password', [
            'email' => $user->email,
        ]);
    }

    /**
     * @param User $user
     * @return string
     */
    protected function makePasswordResetToken(User $user): string
    {
        $this->requestPasswordResetToken($user)->assertSuccessful();

        return Notification::sent($user, UserResetPassword::class)->first()->token;
    }
}
