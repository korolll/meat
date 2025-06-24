<?php

namespace App\Http\Controllers\API\Profile;

use App\Events\DriverRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\DriverStoreRequest;
use App\Http\Requests\DriverUpdateRequest;
use App\Http\Resources\DriverResource;
use App\Http\Responses\DriverCollectionResponse;
use App\Models\Driver;

class DriverController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', Driver::class);

        return DriverCollectionResponse::create(
            $this->user->drivers()
        );
    }

    /**
     * @param DriverStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(DriverStoreRequest $request)
    {
        $this->authorize('create', Driver::class);

        $driver = new Driver($request->validated());
        $driver->user()->associate($this->user);
        $driver->saveOrFail();

        DriverRegistered::dispatch($driver, $request->password);

        return DriverResource::make($driver);
    }

    /**
     * @param Driver $driver
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Driver $driver)
    {
        $this->authorize('view', $driver);

        return DriverResource::make($driver);
    }

    /**
     * @param DriverUpdateRequest $request
     * @param Driver $driver
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(DriverUpdateRequest $request, Driver $driver)
    {
        $this->authorize('update', $driver);

        $driver->fill($request->validated());
        $driver->saveOrFail();

        return DriverResource::make($driver);
    }

    /**
     * @param Driver $driver
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(Driver $driver)
    {
        $this->authorize('delete', $driver);

        $driver->delete();

        return DriverResource::make($driver);
    }
}
