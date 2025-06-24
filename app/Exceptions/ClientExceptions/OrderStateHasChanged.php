<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderStateHasChanged extends ClientException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("Can't change an order state: state has already changed");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2002;
    }
}
