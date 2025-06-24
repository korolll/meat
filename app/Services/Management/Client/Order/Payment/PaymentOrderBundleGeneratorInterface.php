<?php

namespace App\Services\Management\Client\Order\Payment;

use App\Models\Client;
use App\Models\Order;

interface PaymentOrderBundleGeneratorInterface
{
    public function generate(Client $client, Order $order): array;
}
