<?php

namespace App\Http\Resources;

use App\Models\Receipt;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReceiptResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'user' => function (Relation $query) {
                return $query->select('uuid', 'brand_name', 'address');
            },
            'loyaltyCard' => function (Relation $query) {
                return $query->select('uuid', 'client_uuid');
            },
            'user.loyaltyCardTypes' => function (Relation $query) {
                return $query->select('uuid');
            },
        ]);
    }

    /**
     * @param Receipt $receipt
     * @return array
     */
    public function resource($receipt)
    {
        $card = $receipt->loyaltyCard;
        return [
            'uuid' => $receipt->uuid,
            'id' => $receipt->id,
            'total' => $receipt->total,
            'created_at' => $receipt->created_at,
            'store_brand_name' => $receipt->user->brand_name,
            'store_address' => $receipt->user->address,
            'loyalty_card_uuid' => $receipt->loyalty_card_uuid,
            'client_uuid' => $card ? $card->client_uuid : null,
            'receipt_lines_count' => $this->when(isset($receipt->receipt_lines_count), $receipt->receipt_lines_count),
            'receipt_lines_total_discount' => $this->when(isset($receipt->receipt_lines_total_discount), $receipt->receipt_lines_total_discount),
            'receipt_lines_total_weight' => $this->when(isset($receipt->receipt_lines_total_weight), $receipt->receipt_lines_total_weight),
            'loyalty_card_types' => $receipt->user->loyaltyCardTypes->map->only('uuid')->all(),
            'loyalty_card_number' => $receipt->loyalty_card_number,
            'is_refund' => $receipt->refund_by_receipt_uuid !== null,

            'total_bonus' => $receipt->total_bonus,
            'paid_bonus' => $receipt->paid_bonus,
            'bonus_to_charge' => $receipt->bonus_to_charge,
        ];
    }
}
