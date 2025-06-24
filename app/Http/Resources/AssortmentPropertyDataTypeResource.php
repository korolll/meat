<?php

namespace App\Http\Resources;

use App\Models\AssortmentPropertyDataType;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class AssortmentPropertyDataTypeResource extends JsonResource
{
    /**
     * @param AssortmentPropertyDataType $assortmentPropertyDataType
     * @return array
     */
    public function resource($assortmentPropertyDataType)
    {
        return [
            'id' => $assortmentPropertyDataType->id,
            'name' => $assortmentPropertyDataType->name
        ];
    }
}
