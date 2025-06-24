<?php

namespace App\Http\Resources;

use App\Models\LaboratoryTestAppealType;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class LaboratoryTestAppealTypeResource extends JsonResource
{
    /**
     * @param LaboratoryTestAppealType $laboratoryTestAppealType
     * @return array
     */
    public function resource($laboratoryTestAppealType)
    {
        return [
            'uuid' => $laboratoryTestAppealType->uuid,
            'name' => $laboratoryTestAppealType->name,
            'created_at' => $laboratoryTestAppealType->created_at,
        ];
    }
}
