<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class VisitedTransportationPointReorderException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Visited transportation points cannot be reordered');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1024;
    }
}
