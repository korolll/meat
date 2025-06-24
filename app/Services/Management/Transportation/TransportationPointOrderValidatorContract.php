<?php

namespace App\Services\Management\Transportation;

use Illuminate\Support\Collection;

interface TransportationPointOrderValidatorContract
{
    /**
     * @param Collection $transportationPoints
     * @return Collection
     * @throws \App\Exceptions\TealsyException
     */
    public function validate(Collection $transportationPoints);
}
