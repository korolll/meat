<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class ClientNotFoundException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Client not found');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1028;
    }
}
