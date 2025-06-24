<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ClientExceptions\PasswordResetTokenInvalidException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserPasswordResetRequest;
use App\Http\Requests\UserPasswordResetSetPasswordRequest;
use App\Http\Requests\UserPasswordResetValidateTokenRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class UserPasswordResetController extends Controller
{
    /**
     * @var \Illuminate\Auth\Passwords\PasswordBroker
     */
    protected $broker;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->broker = resolve('auth.password')->broker('users');
    }

    /**
     * @param UserPasswordResetRequest $request
     * @return mixed
     * @throws \Throwable
     */
    public function resetPassword(UserPasswordResetRequest $request)
    {
        $result = $this->broker->sendResetLink($request->validated());

        if ($result !== PasswordBroker::RESET_LINK_SENT) {
            throw new ModelNotFoundException();
        }

        return response(['message' => 'Password reset link was successfully sent'], Response::HTTP_CREATED);
    }

    /**
     * @param UserPasswordResetSetPasswordRequest $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    public function setPassword(UserPasswordResetSetPasswordRequest $request)
    {
        $result = $this->broker->reset($request->validated(), function (User $user, string $password) {
            $user->password = $password;
            $user->saveOrFail();
        });

        if ($result !== PasswordBroker::PASSWORD_RESET) {
            throw new PasswordResetTokenInvalidException();
        }

        return response(['message' => 'Password was successfully updated'], Response::HTTP_CREATED);
    }

    /**
     * @param UserPasswordResetValidateTokenRequest $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    public function validateToken(UserPasswordResetValidateTokenRequest $request)
    {
        $credentials = $request->validated();

        if (($user = $this->broker->getUser($credentials)) === null) {
            throw new ModelNotFoundException();
        }

        if ($this->broker->tokenExists($user, Arr::get($credentials, 'token')) === false) {
            throw new PasswordResetTokenInvalidException();
        }

        return response(['message' => 'Password reset token is valid'], Response::HTTP_OK);
    }
}
