<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class PromocodeResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
    }

    /**
     * @param \App\Models\Promocode $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'uuid' => $resource->uuid,
            'name' => $resource->name,
            'description' => $resource->description,
            'discount_percent' => $resource->discount_percent,
            'min_price' => $resource->min_price,
            'enabled' => $resource->enabled,
            'start_at' => $resource->start_at,
            'end_at' => $resource->end_at,
        ];
    }
}
