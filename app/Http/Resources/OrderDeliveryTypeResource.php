<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class OrderDeliveryTypeResource extends JsonResource
{
    /**
     * @param \App\Models\OrderDeliveryType $type
     *
     * @return array
     */
    public function resource($type)
    {
        return [
            'id' => $type->id,
            'name' => $type->name
        ];
    }
}
