<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderShouldBePaid extends ClientException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("Order should be paid");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2005;
    }
}
