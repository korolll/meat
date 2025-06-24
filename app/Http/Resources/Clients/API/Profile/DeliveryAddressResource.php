<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class DeliveryAddressResource extends JsonResource
{
    /**
     * @param \App\Models\ClientDeliveryAddress $resource
     *
     * @return array
     */
    public function resource($resource)
    {
        return [
            'uuid' => $resource->uuid,

            'title' => $resource->title,
            'city' => $resource->city,
            'street' => $resource->street,
            'house' => $resource->house,
            'floor' => $resource->floor,
            'entrance' => $resource->entrance,
            'apartment_number' => $resource->apartment_number,
            'intercom_code' => $resource->intercom_code,

            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
