<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class ClientDoesNotHaveEnoughBonusBalance extends ClientException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("Client does not have enough bonus balance");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2009;
    }
}
