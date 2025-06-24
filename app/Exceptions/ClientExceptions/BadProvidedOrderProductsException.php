<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class BadProvidedOrderProductsException extends ClientException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("Bad provided order products");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2001;
    }
}
