<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class VisitUnloadingPointBeforeLoadingPointException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Driver cannot visit unloading point before loading point');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1025;
    }
}
