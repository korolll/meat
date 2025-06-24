<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;

class StatusTransitionImpossibleException extends ClientException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Can\'t change the status in this way');
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1021;
    }
}
