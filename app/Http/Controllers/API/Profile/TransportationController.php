<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransportationSetStartedRequest;
use App\Http\Requests\TransportationStoreRequest;
use App\Http\Requests\TransportationUpdateRequest;
use App\Http\Resources\TransportationResource;
use App\Http\Responses\TransportationCollectionResponse;
use App\Models\Transportation;
use App\Services\Management\Transportation\TransportationPointFactoryContract;

class TransportationController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', Transportation::class);

        return TransportationCollectionResponse::create(
            $this->user->transportations()
        );
    }

    /**
     * @param TransportationStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(TransportationStoreRequest $request)
    {
        $this->authorize('create', Transportation::class);

        $transportation = new Transportation($request->validated());
        $transportation->user()->associate($this->user);

        app(TransportationPointFactoryContract::class, compact('transportation'))
            ->setProductRequests($request->getProductRequests())
            ->create();

        return TransportationResource::make($transportation);
    }

    /**
     * @param Transportation $transportation
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Transportation $transportation)
    {
        $this->authorize('view', $transportation);

        return TransportationResource::make($transportation);
    }

    /**
     * @param TransportationUpdateRequest $request
     * @param Transportation $transportation
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(TransportationUpdateRequest $request, Transportation $transportation)
    {
        $this->authorize('update', $transportation);

        $transportation->fill($request->validated());
        $transportation->saveOrFail();

        return TransportationResource::make($transportation);
    }

    /**
     * @param TransportationSetStartedRequest $request
     * @param Transportation $transportation
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setStarted(TransportationSetStartedRequest $request, Transportation $transportation)
    {
        $this->authorize('set-started', $transportation);

        $transportation->start($request->started_at);
        $transportation->saveOrFail();

        return TransportationResource::make($transportation);
    }
}
