<?php

namespace App\Http\Resources;

use App\Models\AssortmentProperty;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class AssortmentPropertyResource extends JsonResource
{
    /**
     * @param AssortmentProperty $assortmentProperty
     * @return array
     */
    public function resource($assortmentProperty)
    {
        $pivot = optional($assortmentProperty->pivot);

        return [
            'uuid' => $assortmentProperty->uuid,
            'name' => $assortmentProperty->name,
            'assortment_property_data_type_id' => $assortmentProperty->assortment_property_data_type_id,
            'available_values' => $assortmentProperty->available_values,
            'is_searchable' => $assortmentProperty->is_searchable,

            $this->mergeWhen(isset($pivot->value), function () use ($pivot) {
                return [
                    'value' => $pivot->value,
                ];
            }),
        ];
    }
}
