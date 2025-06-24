<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OnboardingStoreRequest;
use App\Http\Resources\OnboardingResource;
use App\Http\Responses\OnboardingCollectionResponse;
use App\Models\Onboarding;

class OnboardingController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
//        $this->authorize('index', Onboarding::class);

        return OnboardingCollectionResponse::create(
            Onboarding::query()
        );
    }
}
