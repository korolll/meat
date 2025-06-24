<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class ClientBonusTransactionResource extends JsonResource
{
    /**
     * @param \App\Models\ClientBonusTransaction $transaction
     *
     * @return array
     */
    public function resource($transaction)
    {
        return [
            'uuid' => $transaction->uuid,
            'client_uuid' => $transaction->client_uuid,

            'related_reference_id' => $transaction->related_reference_id,
            'related_reference_type' => $transaction->related_reference_type,
            'reason' => $transaction->reason,

            'quantity_old' => $transaction->quantity_old,
            'quantity_new' => $transaction->quantity_new,
            'quantity_delta' => $transaction->quantity_delta,

            'created_at' => $transaction->created_at,
        ];
    }
}
