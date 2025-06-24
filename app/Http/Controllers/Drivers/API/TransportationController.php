<?php

namespace App\Http\Controllers\Drivers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransportationResource;
use App\Http\Responses\TransportationCollectionResponse;
use App\Models\Transportation;

class TransportationController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    public function index()
    {
        return TransportationCollectionResponse::create(
            $this->driver->transportations()
        );
    }

    /**
     * @param Transportation $transportation
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setStarted(Transportation $transportation)
    {
        $this->authorize('set-started', $transportation);

        $transportation->start();
        $transportation->saveOrFail();

        return TransportationResource::make($transportation);
    }
}
