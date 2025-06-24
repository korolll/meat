<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderStateIsFinal extends ClientException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("Can't change an order: the order state is final");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2003;
    }
}
