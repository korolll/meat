<?php

namespace App\Http\Resources;

use App\Models\LaboratoryTestAppealType;
use App\Models\LaboratoryTestStatus;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class LaboratoryTestStatusResource extends JsonResource
{
    /**
     * @param LaboratoryTestStatus $laboratoryTestStatus
     * @return array
     */
    public function resource($laboratoryTestStatus)
    {
        return [
            'id' => $laboratoryTestStatus->id,
            'name' => $laboratoryTestStatus->name
        ];
    }
}
