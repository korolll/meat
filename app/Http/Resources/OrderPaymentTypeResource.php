<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class OrderPaymentTypeResource extends JsonResource
{
    /**
     * @param \App\Models\OrderPaymentType $type
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
