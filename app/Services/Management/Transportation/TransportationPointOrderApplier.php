<?php

namespace App\Services\Management\Transportation;

use App\Exceptions\ClientExceptions\VisitedTransportationPointReorderException;
use App\Models\TransportationPoint;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class TransportationPointOrderApplier implements TransportationPointOrderApplierContract
{
    /**
     * @var Collection|TransportationPoint[]
     */
    protected $transportationPoints;

    /**
     * @var TransportationPointOrderValidatorContract
     */
    protected $validator;

    /**
     * @var int[]
     */
    protected $transportationPointsOrder;

    /**
     * @param Collection|TransportationPoint[] $transportationPoints
     * @param TransportationPointOrderValidatorContract $validator
     */
    public function __construct(Collection $transportationPoints, TransportationPointOrderValidatorContract $validator)
    {
        $this->transportationPoints = $transportationPoints;
        $this->validator = $validator;
    }

    /**
     * @param array $orderedTransportationPointUuids
     * @return $this
     */
    public function setOrderedTransportationPointUuids(array $orderedTransportationPointUuids)
    {
        $this->transportationPointsOrder = array_flip(
            array_values($orderedTransportationPointUuids)
        );

        return $this;
    }

    /**
     * @return Collection|TransportationPoint[]
     * @throws \App\Exceptions\TealsyException
     */
    public function apply()
    {
        $this->transportationPoints->each(function (TransportationPoint $point) {
            $this->processPoint($point);
        });

        return $this->validator->validate(
            $this->transportationPoints->sortBy('order')
        );
    }

    /**
     * @param TransportationPoint $point
     * @throws \App\Exceptions\TealsyException
     */
    protected function processPoint(TransportationPoint $point)
    {
        $order = $this->getPointOrder($point);

        if ($point->order === $order) {
            return;
        }

        if ($point->arrived_at) {
            throw new VisitedTransportationPointReorderException();
        }

        $point->order = $order;
    }

    /**
     * @param TransportationPoint $point
     * @return int
     */
    protected function getPointOrder(TransportationPoint $point)
    {
        return Arr::get($this->transportationPointsOrder, $point->uuid, PHP_INT_MAX);
    }
}
