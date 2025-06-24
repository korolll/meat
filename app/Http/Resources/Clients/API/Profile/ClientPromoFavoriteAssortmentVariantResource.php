<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class ClientPromoFavoriteAssortmentVariantResource extends JsonResource
{
//    /**
//     * @param mixed $resource
//     */
//    public static function loadMissing($resource)
//    {
//
//    }

    /**
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $resource
     *
     * @return array
     */
    public function resource($resource)
    {
        return [
            'uuid' => $resource->uuid,

            'client_uuid' => $resource->client_uuid,
            'can_be_activated_till' => $resource->can_be_activated_till,

            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
