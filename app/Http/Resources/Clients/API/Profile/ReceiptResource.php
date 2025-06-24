<?php

namespace App\Http\Resources\Clients\API\Profile;

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
        return [
            'uuid' => $receipt->uuid,
            'id' => $receipt->id,
            'total' => $receipt->total,
            'created_at' => $receipt->created_at,
            'store_brand_name' => $receipt->user->brand_name,
            'store_address' => $receipt->user->address,
            'receipt_lines_count' => $this->when(isset($receipt->receipt_lines_count), $receipt->receipt_lines_count),
            'loyalty_card_types' => $receipt->user->loyaltyCardTypes->map->only('uuid')->all(),
            'refund_by_receipt_uuid' => $receipt->refund_by_receipt_uuid,

            'total_bonus' => $receipt->total_bonus,
            'paid_bonus' => $receipt->paid_bonus,
            'bonus_to_charge' => $receipt->bonus_to_charge,
        ];
    }
}
