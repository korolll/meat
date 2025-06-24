<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromocodeStoreRequest;
use App\Http\Resources\PromocodeResource;
use App\Http\Responses\PromocodeCollectionResponse;
use App\Models\Promocode;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class PromocodeController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Promocode::class);
        return PromocodeCollectionResponse::create(Promocode::query());
    }

    /**
     * @param \App\Http\Requests\PromocodeStoreRequest $request
     *
     * @return \App\Http\Resources\PromocodeResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(PromocodeStoreRequest $request)
    {
        $this->authorize('create', Promocode::class);

        $validated = $request->validated();
        $promocode = new Promocode($validated);
        $promocode->uuid = Uuid::uuid4();
        $promocode->save();

        return PromocodeResource::make($promocode);
    }

    /**
     * @param \App\Models\Promocode $promocode
     *
     * @return \App\Http\Resources\PromocodeResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Promocode $promocode)
    {
        $this->authorize('view', $promocode);
        return PromocodeResource::make($promocode);
    }

    /**
     * @param \App\Http\Requests\PromocodeStoreRequest $request
     * @param \App\Models\Promocode                    $promocode
     *
     * @return \App\Http\Resources\PromocodeResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(PromocodeStoreRequest $request, Promocode $promocode)
    {
        $this->authorize('update', $promocode);

        $validated = $request->validated();
        $promocode->fill($validated);
        $promocode->save();

        return PromocodeResource::make($promocode);
    }

    /**
     * @param \App\Models\Promocode $promocode
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Promocode $promocode)
    {
        $this->authorize('delete', $promocode);
        $promocode->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
