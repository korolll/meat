<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class FuturePriceListAlreadyExistsException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Future price list is already exists');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1010;
    }
}
