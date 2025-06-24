<?php

namespace App\Services\Models\Assortment\Property\Cast;

use App\Contracts\Models\Assortment\Property\CastDataTypeContract;
use App\Contracts\Models\Assortment\Property\FindUniqueValuesContract;
use App\Models\AssortmentProperty;
use App\Models\AssortmentPropertyDataType;

abstract class AbstractToEnum implements CastDataTypeContract
{
    /**
     * @var FindUniqueValuesContract
     */
    private $finder;

    /**
     * AbstractToEnum constructor.
     * @param FindUniqueValuesContract $finder
     */
    public function __construct(FindUniqueValuesContract $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param AssortmentProperty $assortmentProperty
     * @return AssortmentProperty
     */
    public function cast(AssortmentProperty $assortmentProperty): AssortmentProperty
    {
        $assortmentProperty->available_values = $this->finder->find($assortmentProperty);
        $assortmentProperty->assortment_property_data_type_id = AssortmentPropertyDataType::ID_ENUM;
        $assortmentProperty->save();
        return $assortmentProperty;
    }
}
