<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class OrderClientPaymentResource extends JsonResource
{
    /**
     * @param \App\Models\ClientPayment $payment
     *
     * @return array
     */
    public function resource($payment)
    {
        return [
            'uuid' => $payment->uuid,
            'error_message' => $payment->error_message,
            'amount' => $payment->amount,
            'refunded_amount' => $payment->refunded_amount,
            'order_status' => $payment->order_status,
            'created_at' => $payment->created_at,
        ];
    }
}
