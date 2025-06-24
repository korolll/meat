<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class AuthenticationCodeInvalidException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Invalid authentication code');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1004;
    }
}
