<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class CreditCardResource extends JsonResource
{
    /**
     * @param \App\Models\ClientCreditCard $resource
     *
     * @return array
     */
    public function resource($resource)
    {
        return [
            'uuid' => $resource->uuid,
            'card_mask' => $resource->card_mask,
            'payment_vendor_id' => $resource->payment_vendor_id
        ];
    }
}
