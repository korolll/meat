<?php

namespace App\Services\Management\Client\Product;

use App\Models\Client;
use Carbon\CarbonInterface;

interface CalculateContextInterface
{
    public function getClient(): Client;

    public function getMoment(): ?CarbonInterface;

    public function getTarget(): TargetEnum;
}