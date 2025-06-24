<?php

namespace App\Http\Resources;

use App\Models\AssortmentUnit;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class AssortmentUnitResource extends JsonResource
{
    /**
     * @param AssortmentUnit $assortmentUnit
     * @return array
     */
    public function resource($assortmentUnit)
    {
        return [
            'id' => $assortmentUnit->id,
            'name' => $assortmentUnit->name,
            'short_name' => $assortmentUnit->short_name,
        ];
    }
}
