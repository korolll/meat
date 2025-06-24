<?php

namespace App\Http\Resources;

use App\Models\Receipt;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class ReportReceiptsSalesProductResource extends JsonResource
{
    /**
     * @param Receipt $receipt
     * @return array
     */
    public function resource($receipt)
    {
        return [
            'id' => $receipt->id,
            'date' => (string) Date::parse($receipt->created_at),
            'total' => $receipt->total,
            'receiptLines' => ReportReceiptLinesSalesProductResource::collection($receipt->receiptLines)
        ];
    }
}
