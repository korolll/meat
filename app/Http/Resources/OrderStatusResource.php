<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class OrderStatusResource extends JsonResource
{
    /**
     * @param \App\Models\OrderStatus $status
     *
     * @return array
     */
    public function resource($status)
    {
        return [
            'id' => $status->id,
            'name' => $status->name
        ];
    }
}
