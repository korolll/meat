<?php

namespace App\Http\Resources;

use App\Models\TransportationPoint;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class TransportationPointResource extends JsonResource
{
    /**
     * @param TransportationPoint $transportationPoint
     * @return array
     */
    public function resource($transportationPoint)
    {
        return [
            'uuid' => $transportationPoint->uuid,
            'product_request_uuid' => $transportationPoint->product_request_uuid,
            'transportation_point_type_id' => $transportationPoint->transportation_point_type_id,
            'address' => $transportationPoint->address,
            'arrived_at' => $transportationPoint->arrived_at,
        ];
    }
}
