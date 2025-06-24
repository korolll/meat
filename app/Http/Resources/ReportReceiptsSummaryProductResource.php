<?php

namespace App\Http\Resources;

use App\Models\Receipt;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class ReportReceiptsSummaryProductResource extends JsonResource
{
    /**
     * @param Receipt $receipt
     * @return array
     */
    public function resource($receipt)
    {
        return [
            'date' => Date::parse($receipt->date),
            'quantity' => $receipt->quantity,
            'total' => (int) $receipt->total,
        ];
    }
}
