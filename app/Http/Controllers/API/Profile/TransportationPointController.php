<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransportationPointSetArrivedRequest;
use App\Http\Requests\TransportationPointSetOrderRequest;
use App\Http\Resources\TransportationPointResource;
use App\Http\Responses\TransportationPointCollectionResponse;
use App\Models\Transportation;
use App\Models\TransportationPoint;
use App\Services\Management\Transportation\TransportationPointOrderApplierContract;
use Illuminate\Http\Response;
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

        return TransportationPointCollectionResponse::create(
            $transportation->transportationPoints()
        );
    }

    /**
     * @param TransportationPointSetArrivedRequest $request
     * @param Transportation $transportation
     * @param TransportationPoint $point
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setArrived(
        TransportationPointSetArrivedRequest $request,
        Transportation $transportation,
        TransportationPoint $point
    ) {
        $this->authorize('set-arrived', [$transportation, $point]);

        DB::transaction(function () use ($request, $transportation, $point) {
            $point->arrived_at = $request->arrived_at;
            $point->save();

            $transportation->tryFinish()->save();
        });

        return TransportationPointResource::make($point);
    }

    /**
     * @param TransportationPointSetOrderRequest $request
     * @param Transportation $transportation
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setOrder(TransportationPointSetOrderRequest $request, Transportation $transportation)
    {
        $this->authorize('update', $transportation);

        $transportationPoints = $transportation->transportationPoints;

        app(TransportationPointOrderApplierContract::class, compact('transportationPoints'))
            ->setOrderedTransportationPointUuids($request->getOrderedTransportationPointUuids())
            ->apply();

        $transportationPoints->saveOrFail();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
