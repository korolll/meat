<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialStoreRequest;
use App\Http\Resources\SocialResource;
use App\Http\Responses\SocialCollectionResponse;
use App\Models\Social;
use Illuminate\Http\Response;

class SocialController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
//        $this->authorize('index', Social::class);

        return SocialCollectionResponse::create(
            Social::query()
        );
    }

    /**
     * @param SocialStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(SocialStoreRequest $request)
    {
        $this->authorize('create', Social::class);

        $social = new Social($request->validated());
        $social->saveOrFail();

        return SocialResource::make($social);
    }

    /**
     * @param Social $social
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Social $social)
    {
        $this->authorize('view', $social);

        return SocialResource::make($social);
    }

    /**
     * @param SocialStoreRequest $request
     * @param Social $social
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(SocialStoreRequest $request, Social $social)
    {
        $this->authorize('update', $social);

        $social->fill($request->validated());
        $social->saveOrFail();

        return SocialResource::make($social);
    }

    /**
     * @param \App\Models\Social $social
     *
     * @return \App\Http\Resources\SocialResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Social $social)
    {
        $this->authorize('delete', $social);
        $social->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
