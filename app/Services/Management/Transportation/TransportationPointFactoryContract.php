<?php

namespace App\Services\Management\Transportation;

use App\Models\ProductRequest;
use App\Models\TransportationPoint;
use Illuminate\Support\Collection;

interface TransportationPointFactoryContract
{
    /**
     * @param Collection|ProductRequest[] $productRequests
     * @return $this
     */
    public function setProductRequests(Collection $productRequests);

    /**
     * @return Collection|TransportationPoint[]
     * @throws \Throwable
     */
    public function create();
}
