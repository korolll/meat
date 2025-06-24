<?php

namespace App\Services\Management\Client\Order\Payment;

use App\Models\ClientPayment;
use App\Models\Order;

interface OrderPaymentProcessorInterface
{
    public function process(Order $order): ?ClientPayment;
}
