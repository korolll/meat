<?php

namespace App\Services\Management\Transportation;

use App\Models\TransportationPoint;
use Illuminate\Support\Collection;

interface TransportationPointOrderApplierContract
{
    /**
     * @param array $orderedTransportationPointUuids
     * @return $this
     */
    public function setOrderedTransportationPointUuids(array $orderedTransportationPointUuids);

    /**
     * @return Collection|TransportationPoint[]
     * @throws \App\Exceptions\TealsyException
     */
    public function apply();
}
