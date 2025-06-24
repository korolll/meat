<?php

namespace App\Http\Resources;

use App\Models\LoyaltyCard;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyCodeResource extends JsonResource
{
    /**
     * @param $request *
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'data' => [
                [
                    'uuid' => $this->uuid,
                    'number' => $this->number,
                    'discount_percent' => $this->discount_percent,
                    'loyalty_card_type_uuid' => $this->loyaltyCardType->uuid,
                    'loyalty_card_type_name' => $this->loyaltyCardType->name,
                    'client_uuid' => optional($this->client)->uuid,
                    'client_phone' => optional($this->client)->phone,
                    'client_name' => optional($this->client)->name,
                    'created_at' => $this->created_at,
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'per_page' => 20,
                'to' => 1,
                'total' => 1,
            ]
        ];
    }
}
