<?php

namespace App\Http\Resources;

use App\Models\LoyaltyCardType;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;

class LoyaltyCardTypeResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'logoFile' => function (Relation $query) {
                return $query->select('uuid', 'path');
            },
        ]);
    }

    /**
     * @param LoyaltyCardType $loyaltyCardType
     * @return array
     */
    public function resource($loyaltyCardType)
    {
        return [
            'uuid' => $loyaltyCardType->uuid,
            'name' => $loyaltyCardType->name,
            'logo_file_uuid' => $loyaltyCardType->logoFile->uuid,
            'logo_file_path' => Storage::url($loyaltyCardType->logoFile->path),
            'created_at' => $loyaltyCardType->created_at,
        ];
    }
}
