<?php

namespace App\Http\Controllers\API;

use App\Events\UserVerified;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserVerifyRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\UserCollectionResponse;
use App\Models\User;
use App\Services\Models\User\UserUpdaterInterface;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', User::class);

        return UserCollectionResponse::create(
            User::query()->with(['userAdditionalEmails', 'files'])
        );
    }

    /**
     * @param User $user
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return UserResource::make($user);
    }

    /**
     * @param \App\Http\Requests\ProfileUpdateRequest $request
     * @param \App\Models\User                        $user
     *
     * @return \App\Http\Resources\UserResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(ProfileUpdateRequest $request, User $user)
    {
        $this->authorize('update', $user);

        /** @var UserUpdaterInterface $updater */
        $updater = app(UserUpdaterInterface::class);
        $user = DB::transaction(function () use ($user, $updater, $request) {
            return $updater->update(
                $user,
                $request->validated(),
                $request->getAdditionalEmails(),
                $request->getFiles()
            );
        });

        return UserResource::make($user);
    }

    /**
     * @param UserVerifyRequest $request
     * @param User $user
     * @return mixed
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function verify(UserVerifyRequest $request, User $user)
    {
        $this->authorize('verify', $user);

        $user->user_verify_status_id = $request->user_verify_status_id;
        $user->saveOrFail();

        UserVerified::dispatch($user, $request->comment);

        return UserResource::make($user);
    }
}
