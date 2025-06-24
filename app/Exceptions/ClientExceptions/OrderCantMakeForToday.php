<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderCantMakeForToday extends ClientException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("Can't make order for today");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2012;
    }
}
