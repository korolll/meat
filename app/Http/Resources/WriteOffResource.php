<?php

namespace App\Http\Resources;

use App\Models\WriteOff;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class WriteOffResource extends JsonResource
{
    /**
     * @param WriteOff $writeOff
     * @return array
     */
    public function resource($writeOff)
    {
        return [
            'uuid' => $writeOff->uuid,
            'product_uuid' => $writeOff->product_uuid,
            'write_off_reason_id' => $writeOff->write_off_reason_id,
            'quantity_delta' => $writeOff->quantity_delta,
            'comment' => $writeOff->comment,
        ];
    }
}
