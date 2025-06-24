<?php

namespace App\Http\Resources;

use App\Models\WarehouseTransaction;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class ReportProductsSummaryTransactionResource extends JsonResource
{
    /**
     * @param WarehouseTransaction $transaction
     * @return array
     */
    public function resource($transaction)
    {
        return [
            'uuid' => $transaction->uuid,
            'reference_title' => $transaction->reference_type,
            'quantity_delta' => $transaction->quantity_delta,
            'created_at' => $transaction->created_at,
        ];
    }
}
