<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;
use Carbon\CarbonInterface;

class ExpectedDeliveryDateInvalidException extends ClientException
{
    /**
     * @param \Carbon\CarbonInterface $expectedDeliveryDate
     */
    public function __construct(CarbonInterface $expectedDeliveryDate)
    {
        parent::__construct("It is impossible to ship the goods on {$expectedDeliveryDate}");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1007;
    }
}
