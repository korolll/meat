<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class OrderCantGeocodeClientAddress extends ClientException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct("Can't geocode client address");
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 2006;
    }
}
