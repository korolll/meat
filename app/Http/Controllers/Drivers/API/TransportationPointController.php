<?php

namespace App\Http\Controllers\Drivers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransportationPointResource;
use App\Http\Responses\TransportationPointCollectionResponse;
use App\Models\Transportation;
use App\Models\TransportationPoint;
use Illuminate\Support\Facades\DB;

class TransportationPointController extends Controller
{
    /**
     * @param Transportation $transportation
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Transportation $transportation)
    {
        $this->authorize('view', $transportation);

        return TransportationPointCollectionResponse::create($transportation->transportationPoints());
    }

    /**
     * @param Transportation $transportation
     * @param TransportationPoint $point
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setArrived(Transportation $transportation, TransportationPoint $point)
    {
        $this->authorize('set-arrived', [$transportation, $point]);

        DB::transaction(function () use ($transportation, $point) {
            $point->arrived_at = now();
            $point->save();

            $transportation->tryFinish()->save();
        });

        return TransportationPointResource::make($point);
    }
}
