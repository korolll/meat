<?php

namespace App\Services\Management\Client\Order\Payment\Atol;

use App\Models\Order;

interface AtolSellRequestGeneratorInterface
{
    public function generate(Order $order, bool $isAdvance): array;
}
