<?php

namespace App\Services\Models\Assortment\Property\Cast;

use App\Contracts\Models\Assortment\Property\CastDataTypeContract;
use App\Models\AssortmentProperty;
use App\Models\AssortmentPropertyDataType;

class NumberToString implements CastDataTypeContract
{
    /**
     * @param AssortmentProperty $assortmentProperty
     * @return AssortmentProperty
     */
    public function cast(AssortmentProperty $assortmentProperty): AssortmentProperty
    {
        $assortmentProperty->assortment_property_data_type_id = AssortmentPropertyDataType::ID_STRING;
        $assortmentProperty->save();
        return $assortmentProperty;
    }
}
