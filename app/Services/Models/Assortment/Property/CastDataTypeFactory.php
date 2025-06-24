<?php

namespace App\Services\Models\Assortment\Property;

use App\Contracts\Models\Assortment\Property\CastDataTypeContract;
use App\Contracts\Models\Assortment\Property\CastDataTypeFactoryContract;
use App\Exceptions\ClientExceptions\AssortmentDataTypeCastImpossibleException;
use Illuminate\Support\Str;

class CastDataTypeFactory implements CastDataTypeFactoryContract
{
    /**
     * @param string $currentDataType
     * @param string $newDataType
     * @return CastDataTypeContract
     * @throws AssortmentDataTypeCastImpossibleException
     */
    public function make(string $currentDataType, string $newDataType): CastDataTypeContract
    {
        $path = 'App\Services\Models\Assortment\Property\Cast\\' . Str::studly($currentDataType . '_to_' . $newDataType);
        if (!class_exists($path) || !is_a($path, CastDataTypeContract::class, true)) {
            throw new AssortmentDataTypeCastImpossibleException();
        }

        return app($path);
    }
}
