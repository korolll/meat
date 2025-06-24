<?php

namespace App\Services\Management\Client\Product;

use App\Models\Client;
use Carbon\CarbonInterface;

class CalculateContext implements CalculateContextInterface
{
    public function __construct(
        protected Client $client,
        protected TargetEnum $target,
        protected ?CarbonInterface $moment = null,
    )
    {

    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getMoment(): ?CarbonInterface
    {
        return $this->moment;
    }

    public function getTarget(): TargetEnum
    {
        return $this->target;
    }
}