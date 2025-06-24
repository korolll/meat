<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderClientDoesntHaveCreditCard extends ClientException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("Client doesn't have linked credit card");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2004;
    }
}
