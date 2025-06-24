<?php

namespace App\Services\Money\Acquire\Data;

use App\Services\Management\Client\Order\Payment\PaymentStatusEnum;

class PaymentStatusDto
{
    public function __construct(
        public readonly mixed $originalStatus,
        public readonly PaymentStatusEnum $status,
        public readonly ?string $bindingId,
        public readonly ?string $cardNumberMasked,
    )
    {
    }
}