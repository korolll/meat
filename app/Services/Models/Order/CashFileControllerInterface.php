<?php

namespace App\Services\Models\Order;

use App\Models\Order;

interface CashFileControllerInterface
{
    public function generateFile(Order $order): void;

    public function deleteFile(Order $order): bool;
}
