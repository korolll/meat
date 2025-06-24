<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderClientAddressTooFar extends ClientException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("Client address too far for delivering");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2007;
    }
}
