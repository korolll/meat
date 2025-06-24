<?php

namespace App\Services\Management\Client\Order;

use App\Models\Client;
use App\Models\Order;

interface OrderFactoryInterface
{
    public function make(Client $client, array $attributes, array $assortmentsAttributes): Order;

    public function create(Client $client, array $attributes, array $assortmentsAttributes): Order;
}
