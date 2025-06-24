<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class StoreNotFoundException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Specified store is not found');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1023;
    }
}
