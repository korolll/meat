<?php

namespace App\Services\Money\Acquire\Data;

class CreatedPaymentDto
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $confirmationUrl,
        public readonly PaymentStatusDto $statusDto,
    )
    {

    }
}