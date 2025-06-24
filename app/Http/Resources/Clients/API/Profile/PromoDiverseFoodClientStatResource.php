<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class PromoDiverseFoodClientStatResource extends JsonResource
{
    /**
     * @param \App\Models\PromoDiverseFoodClientStat $resource
     *
     * @return array
     */
    public function resource($resource)
    {
        return [
            'uuid' => $resource->uuid,
            'month' => $resource->month,
            'client_uuid' => $resource->client_uuid,
            'purchased_count' => $resource->purchased_count,
            'rated_count' => $resource->rated_count,
            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
