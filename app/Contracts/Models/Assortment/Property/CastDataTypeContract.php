<?php

namespace App\Contracts\Models\Assortment\Property;

use App\Models\AssortmentProperty;

interface CastDataTypeContract
{
    /**
     * @param AssortmentProperty $assortmentProperty
     * @return AssortmentProperty
     */
    public function cast(AssortmentProperty $assortmentProperty): AssortmentProperty;
}
