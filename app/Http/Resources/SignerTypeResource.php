<?php

namespace App\Http\Resources;

use App\Models\SignerType;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class SignerTypeResource extends JsonResource
{
    /**
     * @param SignerType $signerType
     * @return array
     */
    public function resource($signerType)
    {
        return [
            'id' => $signerType->id,
            'name' => $signerType->name,
        ];
    }
}
