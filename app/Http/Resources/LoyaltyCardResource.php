<?php

namespace App\Http\Resources;

use App\Models\LoyaltyCard;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class LoyaltyCardResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'loyaltyCardType' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'client' => function (Relation $query) {
                return $query->select('uuid', 'phone');
            },
        ]);
    }

    /**
     * @param LoyaltyCard $loyaltyCard
     * @return array
     */
    public function resource($loyaltyCard): array
    {
        return [
            'uuid' => $loyaltyCard->uuid,
            'number' => $loyaltyCard->number,
            'discount_percent' => $loyaltyCard->discount_percent,
            'loyalty_card_type_uuid' => $loyaltyCard->loyaltyCardType->uuid,
            'loyalty_card_type_name' => $loyaltyCard->loyaltyCardType->name,
            'client_uuid' => optional($loyaltyCard->client)->uuid,
            'client_phone' => optional($loyaltyCard->client)->phone,
            'client_name' => optional($loyaltyCard->client)->name,
            'created_at' => $loyaltyCard->created_at,
        ];
    }
}
