<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;
use Carbon\CarbonInterface;

class ExpectedDeliveryDateMustBeGreaterThanException extends ClientException
{
    /**
     * @param \Carbon\CarbonInterface $greaterThan
     */
    public function __construct(CarbonInterface $greaterThan)
    {
        parent::__construct("Expected delivery date must be greater than {$greaterThan}");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1008;
    }
}
