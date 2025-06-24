<?php

namespace App\Contracts\Models\Assortment\Property;

use App\Models\AssortmentProperty;

interface FindUniqueValuesContract
{
    /**
     * @param AssortmentProperty $assortmentProperty
     * @return array
     */
    public function find(AssortmentProperty $assortmentProperty): array;
}
