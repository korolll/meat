<?php

namespace App\Services\Management\Transportation;

use App\Exceptions\ClientExceptions\VisitUnloadingPointBeforeLoadingPointException;
use App\Models\TransportationPoint;
use App\Models\TransportationPointType;
use Illuminate\Support\Collection;

class TransportationPointOrderValidator implements TransportationPointOrderValidatorContract
{
    /**
     * @var array
     */
    protected $validatedProductRequestUuids;

    /**
     * @param Collection $transportationPoints
     * @return Collection
     */
    public function validate(Collection $transportationPoints)
    {
        $this->reset();

        $transportationPoints->each(function (TransportationPoint $point) {
            $this->processPoint($point);
        });

        return $transportationPoints;
    }

    /**
     * @param TransportationPoint $point
     * @throws \App\Exceptions\TealsyException
     */
    protected function processPoint(TransportationPoint $point)
    {
        if (array_search($point->product_request_uuid, $this->validatedProductRequestUuids) !== false) {
            return;
        }

        if ($point->transportation_point_type_id !== TransportationPointType::ID_LOADING) {
            throw new VisitUnloadingPointBeforeLoadingPointException();
        }

        $this->validatedProductRequestUuids[] = $point->product_request_uuid;
    }

    /**
     * @return $this
     */
    protected function reset()
    {
        $this->validatedProductRequestUuids = [];

        return $this;
    }
}
