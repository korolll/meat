<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class PromoDiverseFoodClientDiscountResource extends JsonResource
{
    /**
     * @param \App\Models\PromoDiverseFoodClientDiscount $resource
     *
     * @return array
     */
    public function resource($resource)
    {
        return [
            'uuid' => $resource->uuid,
            'client_uuid' => $resource->client_uuid,
            'discount_percent' => $resource->discount_percent,
            'start_at' => $resource->start_at,
            'end_at' => $resource->end_at,
            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
