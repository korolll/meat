<?php

namespace App\Services\Models\Assortment\Property;

use App\Contracts\Models\Assortment\Property\FindUniqueValuesContract;
use App\Models\AssortmentProperty;

class FindUniqueValues implements FindUniqueValuesContract
{
    /**
     * @param AssortmentProperty $assortmentProperty
     * @return array
     */
    public function find(AssortmentProperty $assortmentProperty): array
    {
        return $assortmentProperty
            ->assortments()
            ->groupBy('assortment_assortment_property.value')
            ->pluck('assortment_assortment_property.value')
            ->all();
    }
}
