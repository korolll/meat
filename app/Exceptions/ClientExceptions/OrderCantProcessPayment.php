<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderCantProcessPayment extends ClientException
{
    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2010;
    }
}
