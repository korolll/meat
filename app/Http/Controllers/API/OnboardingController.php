<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OnboardingStoreRequest;
use App\Http\Resources\OnboardingResource;
use App\Http\Responses\OnboardingCollectionResponse;
use App\Models\Onboarding;
use Illuminate\Http\Response;

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

    /**
     * @param OnboardingStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(OnboardingStoreRequest $request)
    {
        $this->authorize('create', Onboarding::class);

        $onboarding = new Onboarding($request->validated());
        $onboarding->saveOrFail();

        return OnboardingResource::make($onboarding);
    }

    /**
     * @param Onboarding $onboarding
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Onboarding $onboarding)
    {
        $this->authorize('view', $onboarding);

        return OnboardingResource::make($onboarding);
    }

    /**
     * @param OnboardingStoreRequest $request
     * @param Onboarding $onboarding
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(OnboardingStoreRequest $request, Onboarding $onboarding)
    {
        $this->authorize('update', $onboarding);

        $onboarding->fill($request->validated());
        $onboarding->saveOrFail();

        return OnboardingResource::make($onboarding);
    }

    /**
     * @param \App\Models\Onboarding $onboarding
     *
     * @return \App\Http\Resources\OnboardingResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Onboarding $onboarding)
    {
        $this->authorize('delete', $onboarding);
        $onboarding->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
