<?php

namespace App\Http\Resources;

use App\Models\Region;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    /**
     * @param Region $region
     * @return array
     */
    public function resource($region)
    {
        return [
            'uuid' => $region->uuid,
            'name' => $region->name,
        ];
    }
}
