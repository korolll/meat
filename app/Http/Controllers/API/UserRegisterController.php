<?php

namespace App\Http\Controllers\API;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserEmailVerifiedRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserVerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDeliveryZone;
use App\Services\Models\User\UserDeliveryZoneHelper;
use Illuminate\Support\Facades\DB;

class UserRegisterController extends Controller
{
    /**
     * @param UserRegisterRequest $request
     * @return mixed
     * @throws \Throwable
     */
    public function register(UserRegisterRequest $request)
    {
        $properties = $request->validated();
        $zoneData = UserDeliveryZoneHelper::extractZoneData($properties);

        $user = new User($properties);

        DB::transaction(function () use ($user, $zoneData) {
            if ($zoneData) {
                $userDeliveryZone = UserDeliveryZoneHelper::findExistingZone($zoneData);
                if (!$userDeliveryZone) {
                    $userDeliveryZone = UserDeliveryZone::create($zoneData);
                }
                $user->user_delivery_zone_id = $userDeliveryZone->id;
            }

            $user->saveOrFail();
        });

        UserRegistered::dispatch($user);

        return UserResource::make($user);
    }

    /**
     * @param UserVerifyEmailRequest $request
     * @return mixed
     * @throws \Throwable
     */
    public function verifyEmail(UserVerifyEmailRequest $request)
    {
        $user = User::hasEmailVerificationToken($request->token)->firstOrFail();

        if ($user->is_email_verified) {
            throw new \Exception('Email already approved');
        }

        $user->is_email_verified = true;
        $user->password = $request->password;
        $user->save();

        return UserResource::make($user);
    }

    /**
     * @param UserEmailVerifiedRequest $request
     * @return mixed
     * @throws \Throwable
     */
    public function emailVerified(UserEmailVerifiedRequest $request)
    {
        $user = User::hasEmailVerificationToken($request->token)->firstOrFail();

        return [
            'data' => [
                'is_email_verified' => $user->is_email_verified,
                'user_type_id' => $user->user_type_id
            ],
        ];
    }
}
