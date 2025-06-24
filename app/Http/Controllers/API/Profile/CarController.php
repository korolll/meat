<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\CarStoreRequest;
use App\Http\Resources\CarResource;
use App\Http\Responses\CarCollectionResponse;
use App\Models\Car;

class CarController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', Car::class);

        return CarCollectionResponse::create(
            $this->user->cars()
        );
    }

    /**
     * @param CarStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CarStoreRequest $request)
    {
        $this->authorize('create', Car::class);

        $car = new Car($request->validated());
        $car->user()->associate($this->user);
        $car->saveOrFail();

        return CarResource::make($car);
    }

    /**
     * @param Car $car
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Car $car)
    {
        $this->authorize('view', $car);

        return CarResource::make($car);
    }

    /**
     * @param CarStoreRequest $request
     * @param Car $car
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(CarStoreRequest $request, Car $car)
    {
        $this->authorize('update', $car);

        $car->fill($request->validated());
        $car->saveOrFail();

        return CarResource::make($car);
    }

    /**
     * @param Car $car
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(Car $car)
    {
        $this->authorize('delete', $car);

        $car->delete();

        return CarResource::make($car);
    }
}
