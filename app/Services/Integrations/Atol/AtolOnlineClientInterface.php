<?php

namespace App\Services\Integrations\Atol;

interface AtolOnlineClientInterface
{
    public function sell(array $params): array;
    public function sellRefund(array $params): array;
}
