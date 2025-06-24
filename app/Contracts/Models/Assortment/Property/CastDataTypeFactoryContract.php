<?php

namespace App\Contracts\Models\Assortment\Property;

interface CastDataTypeFactoryContract
{
    /**
     * @param string $currentDataType
     * @param string $newDataType
     * @return CastDataTypeContract
     */
    public function make(string $currentDataType, string $newDataType): CastDataTypeContract;
}
